NONAGON LIVE DATABASE DEPLOYMENT

Bundle:
  LIVE_DEPLOY_026_TO_045_2026-10-04.sql

Required baseline:
  The selected database must already contain schema migrations 001 through 025.
  In particular, schema_migrations should end with:
  025_commercial_branding.sql

phpMyAdmin deployment:
  1. Back up the live database.
  2. Select the intended Nonagon database in phpMyAdmin.
  3. Open Import.
  4. Import LIVE_DEPLOY_026_TO_045_2026-10-04.sql.
  5. Confirm the result at the bottom lists migrations 026 through 045.

The bundle does not contain CREATE DATABASE, DROP DATABASE, or USE statements.
It updates only the database selected before import.

Each migration checks schema_migrations before running. Migrations already
recorded there are skipped, so the bundle can continue from a partially
migrated server. The database user needs permission to create and execute
temporary stored procedures during the import; each temporary procedure is
dropped immediately after its migration block.

Before migrations run, a compatibility preflight ensures that the canonical
id columns referenced by new foreign keys are indexed on older imported
schemas (owners, users, equipment, commercial records, and equipment catalog
records). If a unique-index repair fails, check that table for duplicate IDs;
the deployment intentionally stops rather than hiding corrupt identity data.

An object that was created manually without its matching schema_migrations row
is not considered an applied migration. Record that migration accurately first
or repair the partial migration before importing this bundle.
