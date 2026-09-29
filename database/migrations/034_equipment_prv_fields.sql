ALTER TABLE equipment
 ADD COLUMN nominal_size VARCHAR(100) NULL AFTER calibration_status,
 ADD COLUMN connection_specification VARCHAR(150) NULL AFTER nominal_size,
 ADD COLUMN set_pressure DECIMAL(18,6) NULL AFTER connection_specification,
 ADD COLUMN set_pressure_unit VARCHAR(30) NULL AFTER set_pressure,
 ADD COLUMN back_pressure DECIMAL(18,6) NULL AFTER set_pressure_unit,
 ADD COLUMN back_pressure_unit VARCHAR(30) NULL AFTER back_pressure;
