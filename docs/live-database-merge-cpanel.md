# Xinng: one-file cPanel database migration

Prepared 22 October 2026. **Not imported on the live server.** This is for the user's confirmed **empty/new** cPanel target database `lyrivers_xinng` only.

Import this one file, kept outside the web root:

`C:\Users\Aquila Kalagbor\Downloads\lyrivers_xinng_merged_one_import.sql`

- Size: 1,851,023 bytes
- SHA-256: `8F80507577CD4DDD1722B53904DCB0193EA98426C9225D282221DDB123BD0DF1`
- Contains 54 tables and their records. It has no `CREATE DATABASE`, `USE`, or `DROP TABLE` command, so **the selected phpMyAdmin database is the destination**.
- Built from the newer local `migrate_live_lyrivers_xinng.sql` schema/records plus the supplied `lyrivers_xinngqr.sql` schema/records. It was imported successfully into a fresh local MariaDB database, and exact counts matched for all 54 tables. This is not a test against the live MySQL 8 server.

## Import once

1. Confirm the cPanel `lyrivers_xinng` database is empty, that the application is not writing to it, and that the supplied `lyrivers_xinngqr.sql` snapshot is still current. If the source database changed after its 22 October 2026 export, **stop and rebuild from a fresh source export** or those later records will be missing.
2. Back up the source database and any existing target before proceeding. Keep the backups and this SQL file private, outside `public_html`; the export includes personal information, password hashes, and API-token hashes.
3. In phpMyAdmin, select the exact empty `lyrivers_xinng` database in the left sidebar, then use **Import → Choose File** and select `lyrivers_xinng_merged_one_import.sql`. Import it **once**. Do not import either original dump afterward.
4. On an error, stop. Do not rerun the SQL against a partly imported database. Capture the error and restore/recreate an empty target through cPanel before another attempt.

## Data decisions and exceptions

- Source user IDs 1 and 16 share one email; they were merged into the account represented by source ID 1. Source IDs 26 and 27 were merged into source ID 27 because it owns more content. All related content was remapped. `migration_user_map` records every source-to-target ID mapping. Matching accounts already in the newer dump were unified by email; the newer dump's admin role was retained.
- Per the requested policy, a merged account keeps its **primary source account wallet balance and totals**. Duplicate balances were **not added**. Transaction rows were preserved where valid; the source ledger itself is incomplete. `migration_wallet_audit` flags 23 accounts whose stored balance differs from summed ledger rows. Review those differences before treating the ledger as a reconciliation of funds. This import does not grant credits or execute payments.
- The source has no user ID 0, but ID 0 owns seven pages, four short links, and two QR codes. Those records are preserved under a **suspended, non-login** `Unclaimed legacy content` account. Review ownership before making them public or assigning them to a person.
- Seven `signup_bonus` rows refer to nonexistent user 0 and are preserved in `migration_quarantine_credit_transactions`, not the active wallet ledger.
- Eleven short-link click rows refer to ambiguous short-link ID 0 and are preserved in `migration_quarantine_short_link_clicks`, not active analytics. Eleven QR codes had the same ambiguous short-link reference; those QR records remain active with a null `short_link_id`, and the lost association is recorded in `migration_unresolved_references` for review.
- Five old event-detail rows and five old profile rows cannot enter the newer one-row-per-page tables because of missing page ID 0 or duplicate page IDs. They are preserved in `migration_quarantine_page_corporate_events` and `migration_quarantine_page_corporate_profiles`. The latest applicable row is used for each active page.
- The SQL migrates database records, **not image/QR/upload files** referenced by paths. Copy and verify those files separately if the new site does not already have them.

## Verify in phpMyAdmin

Select `lyrivers_xinng`, then run:

```sql
SELECT DATABASE() AS selected_database;
SELECT COUNT(*) AS users FROM users;                         -- 36
SELECT COUNT(*) AS pages FROM pages;                         -- 102
SELECT COUNT(*) AS short_links FROM short_links;             -- 39
SELECT COUNT(*) AS qr_codes FROM qr_codes;                   -- 51
SELECT COUNT(*) AS source_user_mappings FROM migration_user_map; -- 38
SELECT COUNT(*) AS wallet_review_items FROM migration_wallet_audit WHERE difference <> 0; -- 23
SELECT COUNT(*) AS quarantined_bonus_rows FROM migration_quarantine_credit_transactions; -- 7
SELECT COUNT(*) AS quarantined_clicks FROM migration_quarantine_short_link_clicks; -- 11
SELECT COUNT(*) AS unresolved_qr_links FROM migration_unresolved_references; -- 11
```

There should be **54 tables**. Verify sign-in for a merged account (the selected primary source password is authoritative), admin access, representative page/short-link/QR behavior, wallet balances, and referenced media. Do not enable payment or wallet actions until the 23 audit differences have been reviewed. Keep the old source and backups until the cutover is accepted.
