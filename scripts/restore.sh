#!/bin/bash
# =============================================================================
# SDBIP/IDP Database Restore Script
# Restore database from S3 backup
# =============================================================================

set -euo pipefail

# Configuration
RESTORE_DIR="/tmp/sdbip-restore"
S3_BUCKET="${S3_BACKUP_BUCKET:-sdbip-backups}"
ENVIRONMENT="${ENVIRONMENT:-production}"

# Database configuration
DB_HOST="${DB_HOST:-localhost}"
DB_PORT="${DB_PORT:-3306}"
DB_NAME="${DB_NAME:-sdbip_production}"
DB_USER="${DB_USERNAME:-sdbip}"
DB_PASS="${DB_PASSWORD:-}"

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

log() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

warn() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $1"
    exit 1
}

usage() {
    cat << EOF
Usage: $(basename "$0") [OPTIONS]

Restore SDBIP database from S3 backup.

Options:
    -t, --timestamp     Backup timestamp to restore (e.g., 20240115_120000)
    -l, --list          List available backups
    -e, --environment   Environment (staging|production) [default: production]
    -h, --help          Show this help message

Examples:
    $(basename "$0") -l                           # List available backups
    $(basename "$0") -t 20240115_120000          # Restore specific backup
    $(basename "$0") -e staging -t latest        # Restore latest staging backup

EOF
    exit 0
}

list_backups() {
    log "Available backups for ${ENVIRONMENT}:"
    echo ""
    aws s3 ls "s3://${S3_BUCKET}/${ENVIRONMENT}/database/" | \
        sort -r | \
        head -20 | \
        awk '{print "  " $2}' | \
        tr -d '/'
    echo ""
}

find_latest_backup() {
    aws s3 ls "s3://${S3_BUCKET}/${ENVIRONMENT}/database/" | \
        sort -r | \
        head -1 | \
        awk '{print $2}' | \
        tr -d '/'
}

restore_backup() {
    local TIMESTAMP="$1"

    log "Starting restore for timestamp: $TIMESTAMP"

    # Create restore directory
    mkdir -p "$RESTORE_DIR"

    # Download backup files
    log "Downloading backup from S3..."
    local S3_PATH="s3://${S3_BUCKET}/${ENVIRONMENT}/database/${TIMESTAMP}/"

    aws s3 cp "${S3_PATH}" "$RESTORE_DIR/" --recursive

    # Find the backup file
    local BACKUP_FILE
    BACKUP_FILE=$(find "$RESTORE_DIR" -name "*.sql.gz" | head -1)

    if [[ -z "$BACKUP_FILE" ]]; then
        error "No backup file found in ${S3_PATH}"
    fi

    # Verify checksum
    local CHECKSUM_FILE="${BACKUP_FILE}.sha256"
    if [[ -f "$CHECKSUM_FILE" ]]; then
        log "Verifying checksum..."
        local EXPECTED_CHECKSUM
        EXPECTED_CHECKSUM=$(cat "$CHECKSUM_FILE")
        local ACTUAL_CHECKSUM
        ACTUAL_CHECKSUM=$(sha256sum "$BACKUP_FILE" | cut -d' ' -f1)

        if [[ "$EXPECTED_CHECKSUM" != "$ACTUAL_CHECKSUM" ]]; then
            error "Checksum verification failed!"
        fi
        log "Checksum verified successfully."
    else
        warn "No checksum file found, skipping verification."
    fi

    # Decompress
    log "Decompressing backup..."
    gunzip -k "$BACKUP_FILE"
    local SQL_FILE="${BACKUP_FILE%.gz}"

    # Confirmation
    echo ""
    echo -e "${YELLOW}WARNING: This will overwrite the database: $DB_NAME${NC}"
    echo -e "${YELLOW}Environment: $ENVIRONMENT${NC}"
    echo ""
    read -p "Are you sure you want to continue? (yes/no): " confirm

    if [[ "$confirm" != "yes" ]]; then
        log "Restore cancelled by user."
        rm -rf "$RESTORE_DIR"
        exit 0
    fi

    # Restore database
    log "Restoring database..."
    mysql \
        --host="$DB_HOST" \
        --port="$DB_PORT" \
        --user="$DB_USER" \
        --password="$DB_PASS" \
        < "$SQL_FILE"

    log "Database restored successfully!"

    # Clean up
    rm -rf "$RESTORE_DIR"

    # Send notification
    if [[ -n "${SLACK_WEBHOOK:-}" ]]; then
        curl -s -X POST -H 'Content-type: application/json' \
            --data "{\"text\":\"SDBIP Database restored for ${ENVIRONMENT} from backup ${TIMESTAMP}\"}" \
            "$SLACK_WEBHOOK"
    fi

    log "Restore completed."
}

# Parse arguments
TIMESTAMP=""
LIST_ONLY=false

while [[ $# -gt 0 ]]; do
    case $1 in
        -t|--timestamp)
            TIMESTAMP="$2"
            shift 2
            ;;
        -l|--list)
            LIST_ONLY=true
            shift
            ;;
        -e|--environment)
            ENVIRONMENT="$2"
            shift 2
            ;;
        -h|--help)
            usage
            ;;
        *)
            error "Unknown option: $1"
            ;;
    esac
done

# Main
if [[ "$LIST_ONLY" == "true" ]]; then
    list_backups
    exit 0
fi

if [[ -z "$TIMESTAMP" ]]; then
    error "Timestamp is required. Use -t <timestamp> or -l to list available backups."
fi

# Handle 'latest' keyword
if [[ "$TIMESTAMP" == "latest" ]]; then
    TIMESTAMP=$(find_latest_backup)
    if [[ -z "$TIMESTAMP" ]]; then
        error "No backups found for environment: $ENVIRONMENT"
    fi
    log "Using latest backup: $TIMESTAMP"
fi

restore_backup "$TIMESTAMP"
