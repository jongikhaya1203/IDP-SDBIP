#!/bin/bash
# =============================================================================
# SDBIP/IDP Deployment Script
# Production deployment automation for cloud environments
# =============================================================================

set -euo pipefail

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
PROJECT_ROOT="$(dirname "$SCRIPT_DIR")"
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
LOG_FILE="/var/log/sdbip/deploy_${TIMESTAMP}.log"

# Default values
ENVIRONMENT="${ENVIRONMENT:-staging}"
AWS_REGION="${AWS_REGION:-af-south-1}"
IMAGE_TAG="${IMAGE_TAG:-latest}"
DRY_RUN="${DRY_RUN:-false}"

# =============================================================================
# Functions
# =============================================================================

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1" | tee -a "$LOG_FILE"
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1" | tee -a "$LOG_FILE"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1" | tee -a "$LOG_FILE"
    exit 1
}

info() {
    echo -e "${BLUE}[INFO]${NC} $1" | tee -a "$LOG_FILE"
}

usage() {
    cat << EOF
Usage: $(basename "$0") [OPTIONS]

Deploy SDBIP/IDP application to cloud environment.

Options:
    -e, --environment   Target environment (staging|production) [default: staging]
    -t, --tag           Docker image tag to deploy [default: latest]
    -r, --region        AWS region [default: af-south-1]
    -d, --dry-run       Show what would be done without executing
    -h, --help          Show this help message

Examples:
    $(basename "$0") -e staging -t v1.2.3
    $(basename "$0") -e production -t latest
    $(basename "$0") --dry-run -e production

EOF
    exit 0
}

check_prerequisites() {
    log "Checking prerequisites..."

    # Check required tools
    local tools=("aws" "docker" "jq" "curl")
    for tool in "${tools[@]}"; do
        if ! command -v "$tool" &> /dev/null; then
            error "Required tool '$tool' is not installed."
        fi
    done

    # Check AWS credentials
    if ! aws sts get-caller-identity &> /dev/null; then
        error "AWS credentials are not configured or invalid."
    fi

    # Check environment-specific config
    if [[ ! -f "$PROJECT_ROOT/config/environments/${ENVIRONMENT}.env" ]]; then
        warn "Environment config file not found, using defaults."
    fi

    log "Prerequisites check passed."
}

build_image() {
    log "Building Docker image..."

    cd "$PROJECT_ROOT"

    # Build the production image
    docker build \
        --target production \
        --build-arg BUILD_DATE="$(date -u +%Y-%m-%dT%H:%M:%SZ)" \
        --build-arg VCS_REF="$(git rev-parse --short HEAD 2>/dev/null || echo 'unknown')" \
        --build-arg VERSION="${IMAGE_TAG}" \
        -t "sdbip:${IMAGE_TAG}" \
        -t "sdbip:latest" \
        .

    log "Docker image built successfully."
}

push_image() {
    log "Pushing Docker image to ECR..."

    local ECR_REGISTRY
    ECR_REGISTRY=$(aws ecr describe-repositories --repository-names sdbip --query 'repositories[0].repositoryUri' --output text | cut -d'/' -f1)

    # Login to ECR
    aws ecr get-login-password --region "$AWS_REGION" | \
        docker login --username AWS --password-stdin "$ECR_REGISTRY"

    # Tag and push
    docker tag "sdbip:${IMAGE_TAG}" "${ECR_REGISTRY}/sdbip:${IMAGE_TAG}"
    docker tag "sdbip:latest" "${ECR_REGISTRY}/sdbip:latest"

    if [[ "$DRY_RUN" == "false" ]]; then
        docker push "${ECR_REGISTRY}/sdbip:${IMAGE_TAG}"
        docker push "${ECR_REGISTRY}/sdbip:latest"
        log "Image pushed to ECR."
    else
        info "[DRY-RUN] Would push ${ECR_REGISTRY}/sdbip:${IMAGE_TAG}"
    fi
}

run_migrations() {
    log "Running database migrations..."

    local CLUSTER_NAME="sdbip-${ENVIRONMENT}"
    local TASK_DEF="sdbip-migrate"

    if [[ "$DRY_RUN" == "false" ]]; then
        # Run migration task
        aws ecs run-task \
            --cluster "$CLUSTER_NAME" \
            --task-definition "$TASK_DEF" \
            --launch-type FARGATE \
            --network-configuration "awsvpcConfiguration={subnets=[subnet-xxx],securityGroups=[sg-xxx],assignPublicIp=DISABLED}" \
            --region "$AWS_REGION"

        log "Migrations task started."
    else
        info "[DRY-RUN] Would run migrations on cluster $CLUSTER_NAME"
    fi
}

deploy_ecs() {
    log "Deploying to ECS..."

    local CLUSTER_NAME="sdbip-${ENVIRONMENT}"
    local SERVICE_NAME="sdbip-app"

    if [[ "$DRY_RUN" == "false" ]]; then
        # Force new deployment
        aws ecs update-service \
            --cluster "$CLUSTER_NAME" \
            --service "$SERVICE_NAME" \
            --force-new-deployment \
            --region "$AWS_REGION"

        log "Deployment initiated. Waiting for stability..."

        # Wait for deployment to complete
        aws ecs wait services-stable \
            --cluster "$CLUSTER_NAME" \
            --services "$SERVICE_NAME" \
            --region "$AWS_REGION"

        log "Deployment completed successfully."
    else
        info "[DRY-RUN] Would update ECS service $SERVICE_NAME in cluster $CLUSTER_NAME"
    fi
}

invalidate_cache() {
    log "Invalidating CDN cache..."

    if [[ "$ENVIRONMENT" == "production" ]]; then
        local DISTRIBUTION_ID
        DISTRIBUTION_ID=$(aws cloudfront list-distributions --query "DistributionList.Items[?Comment=='sdbip-production'].Id" --output text)

        if [[ -n "$DISTRIBUTION_ID" && "$DRY_RUN" == "false" ]]; then
            aws cloudfront create-invalidation \
                --distribution-id "$DISTRIBUTION_ID" \
                --paths "/*"

            log "CDN cache invalidation initiated."
        else
            info "[DRY-RUN] Would invalidate CloudFront distribution $DISTRIBUTION_ID"
        fi
    fi
}

run_smoke_tests() {
    log "Running smoke tests..."

    local HEALTH_URL
    if [[ "$ENVIRONMENT" == "production" ]]; then
        HEALTH_URL="https://sdbip.municipality.gov.za/health"
    else
        HEALTH_URL="https://staging.sdbip.municipality.gov.za/health"
    fi

    if [[ "$DRY_RUN" == "false" ]]; then
        # Wait for service to be ready
        sleep 30

        local max_attempts=5
        local attempt=1

        while [[ $attempt -le $max_attempts ]]; do
            if curl -sf "$HEALTH_URL" > /dev/null; then
                log "Health check passed."
                return 0
            fi
            warn "Health check attempt $attempt failed, retrying..."
            sleep 10
            ((attempt++))
        done

        error "Health check failed after $max_attempts attempts."
    else
        info "[DRY-RUN] Would check health at $HEALTH_URL"
    fi
}

notify_slack() {
    log "Sending deployment notification..."

    local SLACK_WEBHOOK="${SLACK_WEBHOOK:-}"
    if [[ -z "$SLACK_WEBHOOK" ]]; then
        warn "Slack webhook not configured, skipping notification."
        return
    fi

    local message="SDBIP/IDP ${IMAGE_TAG} deployed to ${ENVIRONMENT} environment"

    if [[ "$DRY_RUN" == "false" ]]; then
        curl -s -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"${message}\"}" \
            "$SLACK_WEBHOOK"

        log "Notification sent."
    else
        info "[DRY-RUN] Would notify: $message"
    fi
}

rollback() {
    warn "Rolling back deployment..."

    local CLUSTER_NAME="sdbip-${ENVIRONMENT}"
    local SERVICE_NAME="sdbip-app"

    # Get previous task definition
    local CURRENT_TASK_DEF
    CURRENT_TASK_DEF=$(aws ecs describe-services \
        --cluster "$CLUSTER_NAME" \
        --services "$SERVICE_NAME" \
        --query 'services[0].taskDefinition' \
        --output text)

    # Extract revision number and decrement
    local TASK_FAMILY
    TASK_FAMILY=$(echo "$CURRENT_TASK_DEF" | cut -d'/' -f2 | cut -d':' -f1)
    local CURRENT_REV
    CURRENT_REV=$(echo "$CURRENT_TASK_DEF" | cut -d':' -f2)
    local PREVIOUS_REV=$((CURRENT_REV - 1))

    if [[ $PREVIOUS_REV -gt 0 ]]; then
        aws ecs update-service \
            --cluster "$CLUSTER_NAME" \
            --service "$SERVICE_NAME" \
            --task-definition "${TASK_FAMILY}:${PREVIOUS_REV}" \
            --region "$AWS_REGION"

        log "Rollback initiated to revision ${PREVIOUS_REV}."
    else
        error "No previous revision available for rollback."
    fi
}

# =============================================================================
# Main
# =============================================================================

main() {
    # Parse arguments
    while [[ $# -gt 0 ]]; do
        case $1 in
            -e|--environment)
                ENVIRONMENT="$2"
                shift 2
                ;;
            -t|--tag)
                IMAGE_TAG="$2"
                shift 2
                ;;
            -r|--region)
                AWS_REGION="$2"
                shift 2
                ;;
            -d|--dry-run)
                DRY_RUN="true"
                shift
                ;;
            -h|--help)
                usage
                ;;
            *)
                error "Unknown option: $1"
                ;;
        esac
    done

    # Validate environment
    if [[ "$ENVIRONMENT" != "staging" && "$ENVIRONMENT" != "production" ]]; then
        error "Invalid environment: $ENVIRONMENT. Must be 'staging' or 'production'."
    fi

    # Create log directory
    mkdir -p "$(dirname "$LOG_FILE")"

    log "=========================================="
    log "SDBIP/IDP Deployment"
    log "=========================================="
    log "Environment: $ENVIRONMENT"
    log "Image Tag:   $IMAGE_TAG"
    log "AWS Region:  $AWS_REGION"
    log "Dry Run:     $DRY_RUN"
    log "=========================================="

    # Confirmation for production
    if [[ "$ENVIRONMENT" == "production" && "$DRY_RUN" == "false" ]]; then
        echo -e "${YELLOW}WARNING: You are about to deploy to PRODUCTION.${NC}"
        read -p "Are you sure you want to continue? (yes/no): " confirm
        if [[ "$confirm" != "yes" ]]; then
            log "Deployment cancelled by user."
            exit 0
        fi
    fi

    # Run deployment steps
    check_prerequisites
    build_image
    push_image
    run_migrations
    deploy_ecs
    invalidate_cache
    run_smoke_tests
    notify_slack

    log "=========================================="
    log "Deployment completed successfully!"
    log "=========================================="
}

# Handle errors
trap 'error "Deployment failed at line $LINENO. Check logs at $LOG_FILE"' ERR

# Run main
main "$@"
