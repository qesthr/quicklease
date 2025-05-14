-- Check if customer_phone column exists and rename it to phone
SET @dbname = 'quicklease_db';
SET @tablename = 'users';
SET @columnname = 'customer_phone';
SET @newcolumnname = 'phone';
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  CONCAT('ALTER TABLE ', @tablename, ' CHANGE ', @columnname, ' ', @newcolumnname, ' VARCHAR(20)'),
  CONCAT('ALTER TABLE ', @tablename, ' ADD ', @newcolumnname, ' VARCHAR(20)')
));

PREPARE alterIfExists FROM @preparedStatement;
EXECUTE alterIfExists;
DEALLOCATE PREPARE alterIfExists; 