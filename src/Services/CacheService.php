<?php
/**
 * Redis Cache Service
 *
 * Provides caching functionality using Redis for the SDBIP/IDP application.
 * Supports key prefixing, TTL management, and cache tagging.
 */

namespace App\Services;

use Redis;
use Exception;

class CacheService
{
    private static ?CacheService $instance = null;
    private ?Redis $redis = null;
    private bool $enabled = true;
    private string $prefix = 'sdbip:';
    private int $defaultTtl = 3600; // 1 hour

    /**
     * Cache TTL constants
     */
    const TTL_SHORT = 300;        // 5 minutes
    const TTL_MEDIUM = 1800;      // 30 minutes
    const TTL_LONG = 3600;        // 1 hour
    const TTL_DAY = 86400;        // 24 hours
    const TTL_WEEK = 604800;      // 7 days

    /**
     * Cache tags for grouped invalidation
     */
    const TAG_KPI = 'kpi';
    const TAG_DASHBOARD = 'dashboard';
    const TAG_REPORTS = 'reports';
    const TAG_USER = 'user';
    const TAG_DIRECTORATE = 'directorate';
    const TAG_BUDGET = 'budget';

    private function __construct()
    {
        $this->connect();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): self
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Connect to Redis server
     */
    private function connect(): void
    {
        if (!extension_loaded('redis')) {
            $this->enabled = false;
            error_log('Redis extension not loaded, caching disabled');
            return;
        }

        try {
            $this->redis = new Redis();

            $host = defined('REDIS_HOST') ? REDIS_HOST : '127.0.0.1';
            $port = defined('REDIS_PORT') ? (int)REDIS_PORT : 6379;
            $password = defined('REDIS_PASSWORD') ? REDIS_PASSWORD : null;
            $database = defined('REDIS_DATABASE') ? (int)REDIS_DATABASE : 0;

            $connected = $this->redis->connect($host, $port, 2.0);

            if (!$connected) {
                throw new Exception("Failed to connect to Redis at {$host}:{$port}");
            }

            if (!empty($password)) {
                $this->redis->auth($password);
            }

            $this->redis->select($database);
            $this->redis->setOption(Redis::OPT_SERIALIZER, Redis::SERIALIZER_PHP);
            $this->redis->setOption(Redis::OPT_PREFIX, $this->prefix);

        } catch (Exception $e) {
            $this->enabled = false;
            error_log('Redis connection failed: ' . $e->getMessage());
        }
    }

    /**
     * Check if caching is available
     */
    public function isEnabled(): bool
    {
        return $this->enabled && $this->redis !== null;
    }

    /**
     * Get a cached value
     */
    public function get(string $key, $default = null)
    {
        if (!$this->isEnabled()) {
            return $default;
        }

        try {
            $value = $this->redis->get($key);
            return $value !== false ? $value : $default;
        } catch (Exception $e) {
            error_log("Cache get error for key '{$key}': " . $e->getMessage());
            return $default;
        }
    }

    /**
     * Set a cached value
     */
    public function set(string $key, $value, int $ttl = null, array $tags = []): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $ttl = $ttl ?? $this->defaultTtl;
            $result = $this->redis->setex($key, $ttl, $value);

            // Add key to tag sets for grouped invalidation
            foreach ($tags as $tag) {
                $this->redis->sAdd("tag:{$tag}", $key);
                $this->redis->expire("tag:{$tag}", self::TTL_WEEK);
            }

            return $result;
        } catch (Exception $e) {
            error_log("Cache set error for key '{$key}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Delete a cached value
     */
    public function delete(string $key): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            return $this->redis->del($key) > 0;
        } catch (Exception $e) {
            error_log("Cache delete error for key '{$key}': " . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if a key exists
     */
    public function exists(string $key): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            return $this->redis->exists($key) > 0;
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Get or set a cached value (cache-aside pattern)
     */
    public function remember(string $key, int $ttl, callable $callback, array $tags = [])
    {
        $value = $this->get($key);

        if ($value !== null) {
            return $value;
        }

        $value = $callback();
        $this->set($key, $value, $ttl, $tags);

        return $value;
    }

    /**
     * Invalidate all keys with a specific tag
     */
    public function invalidateTag(string $tag): int
    {
        if (!$this->isEnabled()) {
            return 0;
        }

        try {
            $keys = $this->redis->sMembers("tag:{$tag}");
            $count = 0;

            if (!empty($keys)) {
                foreach ($keys as $key) {
                    if ($this->redis->del($key) > 0) {
                        $count++;
                    }
                }
                $this->redis->del("tag:{$tag}");
            }

            return $count;
        } catch (Exception $e) {
            error_log("Cache tag invalidation error for tag '{$tag}': " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Clear all cache
     */
    public function flush(): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            return $this->redis->flushDB();
        } catch (Exception $e) {
            error_log('Cache flush error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Increment a counter
     */
    public function increment(string $key, int $value = 1): int
    {
        if (!$this->isEnabled()) {
            return 0;
        }

        try {
            return $this->redis->incrBy($key, $value);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Decrement a counter
     */
    public function decrement(string $key, int $value = 1): int
    {
        if (!$this->isEnabled()) {
            return 0;
        }

        try {
            return $this->redis->decrBy($key, $value);
        } catch (Exception $e) {
            return 0;
        }
    }

    /**
     * Get cache statistics
     */
    public function getStats(): array
    {
        if (!$this->isEnabled()) {
            return ['enabled' => false];
        }

        try {
            $info = $this->redis->info();
            return [
                'enabled' => true,
                'connected' => true,
                'used_memory' => $info['used_memory_human'] ?? 'N/A',
                'connected_clients' => $info['connected_clients'] ?? 0,
                'total_keys' => $this->redis->dbSize(),
                'hits' => $info['keyspace_hits'] ?? 0,
                'misses' => $info['keyspace_misses'] ?? 0,
                'hit_rate' => $this->calculateHitRate($info),
                'uptime_days' => round(($info['uptime_in_seconds'] ?? 0) / 86400, 2),
            ];
        } catch (Exception $e) {
            return ['enabled' => true, 'connected' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Calculate cache hit rate
     */
    private function calculateHitRate(array $info): string
    {
        $hits = $info['keyspace_hits'] ?? 0;
        $misses = $info['keyspace_misses'] ?? 0;
        $total = $hits + $misses;

        if ($total === 0) {
            return 'N/A';
        }

        return round(($hits / $total) * 100, 2) . '%';
    }

    // =========================================================================
    // Domain-Specific Cache Methods
    // =========================================================================

    /**
     * Cache dashboard statistics
     */
    public function cacheDashboardStats(int $financialYearId, array $stats): bool
    {
        $key = "dashboard:stats:{$financialYearId}";
        return $this->set($key, $stats, self::TTL_MEDIUM, [self::TAG_DASHBOARD]);
    }

    /**
     * Get cached dashboard statistics
     */
    public function getDashboardStats(int $financialYearId): ?array
    {
        $key = "dashboard:stats:{$financialYearId}";
        return $this->get($key);
    }

    /**
     * Cache KPI list for a directorate
     */
    public function cacheKPIList(int $directorateId, int $financialYearId, array $kpis): bool
    {
        $key = "kpi:list:{$directorateId}:{$financialYearId}";
        return $this->set($key, $kpis, self::TTL_LONG, [self::TAG_KPI, self::TAG_DIRECTORATE]);
    }

    /**
     * Get cached KPI list
     */
    public function getKPIList(int $directorateId, int $financialYearId): ?array
    {
        $key = "kpi:list:{$directorateId}:{$financialYearId}";
        return $this->get($key);
    }

    /**
     * Cache user permissions
     */
    public function cacheUserPermissions(int $userId, array $permissions): bool
    {
        $key = "user:permissions:{$userId}";
        return $this->set($key, $permissions, self::TTL_LONG, [self::TAG_USER]);
    }

    /**
     * Get cached user permissions
     */
    public function getUserPermissions(int $userId): ?array
    {
        $key = "user:permissions:{$userId}";
        return $this->get($key);
    }

    /**
     * Cache quarterly report
     */
    public function cacheQuarterlyReport(int $financialYearId, int $quarter, array $report): bool
    {
        $key = "report:quarterly:{$financialYearId}:{$quarter}";
        return $this->set($key, $report, self::TTL_DAY, [self::TAG_REPORTS]);
    }

    /**
     * Get cached quarterly report
     */
    public function getQuarterlyReport(int $financialYearId, int $quarter): ?array
    {
        $key = "report:quarterly:{$financialYearId}:{$quarter}";
        return $this->get($key);
    }

    /**
     * Cache budget summary
     */
    public function cacheBudgetSummary(int $financialYearId, array $summary): bool
    {
        $key = "budget:summary:{$financialYearId}";
        return $this->set($key, $summary, self::TTL_MEDIUM, [self::TAG_BUDGET]);
    }

    /**
     * Get cached budget summary
     */
    public function getBudgetSummary(int $financialYearId): ?array
    {
        $key = "budget:summary:{$financialYearId}";
        return $this->get($key);
    }

    /**
     * Invalidate all caches for a specific KPI
     */
    public function invalidateKPI(int $kpiId, int $directorateId = null): void
    {
        $this->delete("kpi:{$kpiId}");
        $this->invalidateTag(self::TAG_DASHBOARD);

        if ($directorateId) {
            $this->delete("kpi:list:{$directorateId}:*");
        }
    }

    /**
     * Invalidate all caches for a user
     */
    public function invalidateUser(int $userId): void
    {
        $this->delete("user:permissions:{$userId}");
        $this->delete("user:profile:{$userId}");
    }

    // =========================================================================
    // Rate Limiting
    // =========================================================================

    /**
     * Check if request is rate limited
     */
    public function isRateLimited(string $identifier, int $maxRequests = 60, int $window = 60): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        $key = "ratelimit:{$identifier}";
        $current = $this->increment($key);

        if ($current === 1) {
            $this->redis->expire($this->prefix . $key, $window);
        }

        return $current > $maxRequests;
    }

    /**
     * Get remaining rate limit
     */
    public function getRateLimitRemaining(string $identifier, int $maxRequests = 60): int
    {
        if (!$this->isEnabled()) {
            return $maxRequests;
        }

        $key = "ratelimit:{$identifier}";
        $current = (int) $this->get($key, 0);

        return max(0, $maxRequests - $current);
    }

    // =========================================================================
    // Session Management
    // =========================================================================

    /**
     * Store session data
     */
    public function setSession(string $sessionId, array $data, int $ttl = 7200): bool
    {
        $key = "session:{$sessionId}";
        return $this->set($key, $data, $ttl);
    }

    /**
     * Get session data
     */
    public function getSession(string $sessionId): ?array
    {
        $key = "session:{$sessionId}";
        return $this->get($key);
    }

    /**
     * Destroy session
     */
    public function destroySession(string $sessionId): bool
    {
        $key = "session:{$sessionId}";
        return $this->delete($key);
    }

    /**
     * Extend session TTL
     */
    public function touchSession(string $sessionId, int $ttl = 7200): bool
    {
        if (!$this->isEnabled()) {
            return false;
        }

        try {
            $key = "session:{$sessionId}";
            return $this->redis->expire($this->prefix . $key, $ttl);
        } catch (Exception $e) {
            return false;
        }
    }
}
