-- Run once against an existing V11 database
ALTER TABLE wallet_ledger MODIFY type ENUM('credit','debit','refund','share_return','withdrawal') NOT NULL;
ALTER TABLE withdrawal_requests ADD COLUMN reference VARCHAR(100) NULL UNIQUE;
ALTER TABLE withdrawal_requests ADD COLUMN reviewed_by BIGINT UNSIGNED NULL;
ALTER TABLE withdrawal_requests ADD COLUMN reviewed_at TIMESTAMP NULL;
ALTER TABLE withdrawal_requests ADD COLUMN reason VARCHAR(255) NULL;
ALTER TABLE withdrawal_requests ADD CONSTRAINT fk_withdraw_reviewer FOREIGN KEY (reviewed_by) REFERENCES users(id) ON DELETE SET NULL;
