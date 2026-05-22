# Shobjiwala local helpers. All targets assume `docker compose` is running.

.PHONY: db-dump
db-dump: ## Dump the running local MariaDB to deploy/snapshots/latest.sql.gz
	@mkdir -p deploy/snapshots
	@docker compose exec -T db sh -c \
	  'mysqldump --single-transaction --routines --triggers --no-tablespaces \
	    -u root -p"$$MARIADB_ROOT_PASSWORD" "$$MARIADB_DATABASE"' \
	  | gzip -9 > deploy/snapshots/latest.sql.gz
	@ls -lh deploy/snapshots/latest.sql.gz
	@echo "Dump ready. Next: commit to snapshot/<date> branch, then run db-push workflow."

.PHONY: db-dump-verify
db-dump-verify: ## Sanity-check the last dump (decompress + grep for table count)
	@test -s deploy/snapshots/latest.sql.gz || { echo "No dump found. Run 'make db-dump' first."; exit 1; }
	@echo "Decompressed size:"
	@gunzip -c deploy/snapshots/latest.sql.gz | wc -c
	@echo "CREATE TABLE count:"
	@gunzip -c deploy/snapshots/latest.sql.gz | grep -c '^CREATE TABLE'
