# =============================================================================
# Azure Terraform Variables - SDBIP/IDP Management System
# =============================================================================

variable "project_name" {
  description = "Project name for resource naming"
  type        = string
  default     = "sdbip"
}

variable "environment" {
  description = "Environment (development, staging, production)"
  type        = string
  default     = "production"

  validation {
    condition     = contains(["development", "staging", "production"], var.environment)
    error_message = "Environment must be development, staging, or production."
  }
}

variable "azure_region" {
  description = "Azure region for deployment"
  type        = string
  default     = "southafricanorth"  # Johannesburg region for SA compliance
}

# =============================================================================
# Networking
# =============================================================================

variable "vnet_cidr" {
  description = "CIDR block for VNet"
  type        = string
  default     = "10.0.0.0/16"
}

variable "app_subnet_cidr" {
  description = "CIDR block for app subnet"
  type        = string
  default     = "10.0.1.0/24"
}

variable "db_subnet_cidr" {
  description = "CIDR block for database subnet"
  type        = string
  default     = "10.0.2.0/24"
}

variable "redis_subnet_cidr" {
  description = "CIDR block for Redis subnet"
  type        = string
  default     = "10.0.3.0/24"
}

# =============================================================================
# Database
# =============================================================================

variable "db_name" {
  description = "Database name"
  type        = string
  default     = "sdbip_production"
}

variable "db_username" {
  description = "Database admin username"
  type        = string
  sensitive   = true
}

variable "db_password" {
  description = "Database admin password"
  type        = string
  sensitive   = true
}

variable "db_sku" {
  description = "MySQL Flexible Server SKU"
  type        = string
  default     = "GP_Standard_D2ds_v4"
}

variable "db_storage_size" {
  description = "Database storage size in GB"
  type        = number
  default     = 128
}

# =============================================================================
# Redis
# =============================================================================

variable "redis_capacity" {
  description = "Redis cache capacity"
  type        = number
  default     = 1
}

variable "redis_family" {
  description = "Redis cache family"
  type        = string
  default     = "C"
}

variable "redis_sku" {
  description = "Redis cache SKU"
  type        = string
  default     = "Standard"
}

# =============================================================================
# Container App
# =============================================================================

variable "container_cpu" {
  description = "Container CPU cores"
  type        = number
  default     = 0.5
}

variable "container_memory" {
  description = "Container memory"
  type        = string
  default     = "1Gi"
}

variable "min_replicas" {
  description = "Minimum container replicas"
  type        = number
  default     = 2
}

variable "max_replicas" {
  description = "Maximum container replicas"
  type        = number
  default     = 10
}

variable "app_version" {
  description = "Application version/tag to deploy"
  type        = string
  default     = "latest"
}

# =============================================================================
# Monitoring
# =============================================================================

variable "alert_email" {
  description = "Email address for alerts"
  type        = string
  default     = ""
}
