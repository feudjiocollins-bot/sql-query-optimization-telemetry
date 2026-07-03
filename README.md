# Enterprise SQL Query Optimization & Telemetry Database

An advanced database administration and performance tuning project simulating a high-traffic enterprise logging system (100K+ records). This project demonstrates how to diagnose critical database bottlenecks and implement precise indexing strategies to drastically reduce server response times.

## 🏗️ System Architecture
The database consists of a relational schema optimized for high-throughput writes:
* **`applications`**: Stores monitored environments (Production, Staging, Dev).
* **`system_logs`**: Time-series log repository recording endpoints, HTTP status codes, error levels, and execution metrics (`response_time_ms`).

## ⚡ Real-World Case Study: Fixing Dashboard Latency

### 1. The Bottleneck (Slow Query)
The system dashboard suffered major slowdowns when generating real-time emergency reports using this query:
```sql
SELECT app_id, endpoint, AVG(response_time_ms) as avg_time, COUNT(*) as total_errors
FROM system_logs
WHERE level = 'ERROR' AND endpoint = '/api/v1/payment'
GROUP BY app_id, endpoint;

2. The Diagnosis (EXPLAIN Analysis)
Running an EXPLAIN query exposed a catastrophic execution plan:
• Scan Type: ALL (Full Table Scan)
• Rows Examined: ~99,606 rows
• Extra Metrics: Using where; Using temporary; Using filesort
• Root Cause: The database engine had to read every single record from disk, creating massive CPU and memory overhead.

3. The Resolution (Composite Indexing)
I designed and deployed a targeted composite index tailored perfectly to the query execution order (Filtering -> Grouping -> Aggregation):
2. The Diagnosis (EXPLAIN Analysis)
Running an EXPLAIN query exposed a catastrophic execution plan:
• Scan Type: ALL (Full Table Scan)
• Rows Examined: ~99,606 rows
• Extra Metrics: Using where; Using temporary; Using filesort
• Root Cause: The database engine had to read every single record from disk, creating massive CPU and memory overhead.
3. The Resolution (Composite Indexing)
I designed and deployed a targeted composite index tailored perfectly to the query execution order (Filtering -> Grouping -> Aggregation):
CREATE INDEX idx_logs_performance ON system_logs (level, endpoint, app_id, response_time_ms);
4. The Results (96.6% Optimization)
Re-evaluating with EXPLAIN confirmed an immediate production-grade improvement:
• Scan Type: Upgraded to ref (Direct memory pointer search)
• Index Utilized: idx_logs_performance
• Rows Examined: Dropped from 99,606 to 3,307 rows.
• Extra: Switched to Using index (Zero disk lookups required; execution completed entirely within the RAM cache in under 3ms).
Developed by Collins F. - Database Performance & Optimization Specialist.
