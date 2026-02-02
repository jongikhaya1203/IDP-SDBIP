#!/bin/bash
# =============================================================================
# SDBIP/IDP Database Backup Script
# Automated backup to S3 with retention management
# =============================================================================

set -euo pipefail

# Configuration
TIMESTAMP=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/tmp/sdbip-backups"
S3_BUCKET="${S3_BACKUP_BUCKET:-sdbip-backups}"
RETENTION_DAYS="${RETENTION_DAYS:-30}"
ENVIRONMENT="${ENVIRONMENT:-production}"

# Database configuration from environment
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

# Create backup directory
mkdir -p "$BACKUP_DIR"

log "Starting database backup..."
log "Database: $DB_NAME"
log "Environment: $ENVIRONMENT"

# Create database dump
BACKUP_FILE="${BACKUP_DIR}/${DB_NAME}_${TIMESTAMP}.sql"
COMPRESSED_FILE="${BACKUP_FILE}.gz"

log "Creating database dump..."
mysqldump \
    --host="$DB_HOST" \
    --port="$DB_PORT" \
    --user="$DB_USER" \
    --password="$DB_PASS" \
    --single-transaction \
    --routines \
    --triggers \
    --events \
    --add-drop-database \
    --databases "$DB_NAME" \
    > "$BACKUP_FILE"

log "Compressing backup..."
gzip -9 "$BACKUP_FILE"

# Calculate checksum
CHECKSUM=$(sha256sum "$COMPRESSED_FILE" | cut -d' ' -f1)
echo "$CHECKSUM" > "${COMPRESSED_FILE}.sha256"

log "Backup file: $COMPRESSED_FILE"
log "Size: $(du -h "$COMPRESSED_FILE" | cut -f1)"
log "Checksum: $CHECKSUM"

# Upload to S3
log "Uploading to S3..."
S3_PATH="s3://${S3_BUCKET}/${ENVIRONMENT}/database/${TIMESTAMP}/"

aws s3 cp "$COMPRESSED_FILE" "${S3_PATH}"
aws s3 cp "${COMPRESSED_FILE}.sha256" "${S3_PATH}"

log "Backup uploaded to ${S3_PATH}"

# Clean up local files
rm -f "$COMPRESSED_FILE" "${COMPRESSED_FILE}.sha256"

# Clean old backups from S3
log "Cleaning backups older than ${RETENTION_DAYS} days..."
CUTOFF_DATE=$(date -d "-${RETENTION_DAYS} days" +%Y%m%d)

aws s3 ls "s3://${S3_BUCKET}/${ENVIRONMENT}/database/" | while read -r line; do
    BACKUP_DATE=$(echo "$line" | awk '{print $2}' | tr -d '/' | cut -c1-8)
    if [[ "$BACKUP_DATE" < "$CUTOFF_DATE" ]]; then
        FOLDER=$(echo "$line" | awk '{print $2}')
        log "Deleting old backup: $FOLDER"
        aws s3 rm "s3://${S3_BUCKET}/${ENVIRONMENT}/database/${FOLDER}" --recursive
    fi
done

log "Backup completed successfully!"

# Backup POE files (if configured)
if [[ "${BACKUP_POE:-false}" == "true" ]]; then
    log "Backing up POE files..."

    POE_BACKUP="${BACKUP_DIR}/poe_${TIMESTAMP}.tar.gz"
    tar -czf "$POE_BACKUP" -C /var/www/html/public/uploads/poe . 2>/dev/null || true

    if [[ -f "$POE_BACKUP" ]]; then
        aws s3 cp "$POE_BACKUP" "s3://${S3_BUCKET}/${ENVIRONMENT}/poe/${TIMESTAMP}/"
        rm -f "$POE_BACKUP"
        log "POE backup completed."
    fi
fi

# Send notification
if [[ -n "${SLACK_WEBHOOK:-}" ]]; then
    curl -s -X POST -H 'Content-type: application/json' \
        --data "{\"text\":\"SDBIP Backup completed for ${ENVIRONMENT}: ${DB_NAME} (${TIMESTAMP})\"}" \
        "$SLACK_WEBHOOK"
fi

log "All backup operations completed."
