ALTER TABLE equipment
 ADD COLUMN measurement_lower DECIMAL(18,6) NULL AFTER long_description,
 ADD COLUMN measurement_upper DECIMAL(18,6) NULL AFTER measurement_lower,
 ADD COLUMN measurement_unit VARCHAR(30) NULL AFTER measurement_upper,
 ADD COLUMN measurement_accuracy VARCHAR(100) NULL AFTER measurement_unit,
 ADD COLUMN calibration_date DATE NULL AFTER measurement_accuracy,
 ADD COLUMN calibration_due_date DATE NULL AFTER calibration_date,
 ADD COLUMN calibration_certificate_number VARCHAR(150) NULL AFTER calibration_due_date,
 ADD COLUMN calibration_status VARCHAR(60) NULL AFTER calibration_certificate_number;
