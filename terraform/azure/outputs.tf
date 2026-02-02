# =============================================================================
# Azure Terraform Outputs - SDBIP/IDP Management System
# =============================================================================

output "resource_group_name" {
  description = "Resource group name"
  value       = azurerm_resource_group.main.name
}

output "vnet_id" {
  description = "Virtual Network ID"
  value       = azurerm_virtual_network.main.id
}

output "mysql_fqdn" {
  description = "MySQL Flexible Server FQDN"
  value       = azurerm_mysql_flexible_server.main.fqdn
}

output "redis_hostname" {
  description = "Redis Cache hostname"
  value       = azurerm_redis_cache.main.hostname
}

output "redis_ssl_port" {
  description = "Redis Cache SSL port"
  value       = azurerm_redis_cache.main.ssl_port
}

output "storage_account_name" {
  description = "Storage account name for POE"
  value       = azurerm_storage_account.poe.name
}

output "storage_container_name" {
  description = "Storage container name for POE"
  value       = azurerm_storage_container.poe.name
}

output "container_registry_url" {
  description = "Container Registry URL"
  value       = azurerm_container_registry.main.login_server
}

output "container_app_url" {
  description = "Container App URL"
  value       = "https://${azurerm_container_app.main.ingress[0].fqdn}"
}

output "frontdoor_endpoint" {
  description = "Front Door endpoint hostname"
  value       = azurerm_cdn_frontdoor_endpoint.main.host_name
}

output "application_insights_key" {
  description = "Application Insights instrumentation key"
  value       = azurerm_application_insights.main.instrumentation_key
  sensitive   = true
}

output "key_vault_uri" {
  description = "Key Vault URI"
  value       = azurerm_key_vault.main.vault_uri
}

output "log_analytics_workspace_id" {
  description = "Log Analytics Workspace ID"
  value       = azurerm_log_analytics_workspace.main.id
}
