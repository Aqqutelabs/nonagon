INSERT INTO marketplace_agreement_templates(id,organization_id,agreement_type,name,version,body_template)
VALUES
(UUID(),NULL,'LEASE','Nonagon standard equipment lease','1.0','NONAGON {{agreement_name}} AGREEMENT\n\nEquipment: {{equipment}}\nSupplier: {{supplier}}\nCustomer: {{customer}}\nValue: {{value}}\n\nThis agreement incorporates the accepted offer terms and the recorded mobilization, insurance, payment, handover and return obligations.'),
(UUID(),NULL,'SALE','Nonagon standard equipment sale','1.0','NONAGON {{agreement_name}} AGREEMENT\n\nEquipment: {{equipment}}\nSupplier: {{supplier}}\nCustomer: {{customer}}\nValue: {{value}}\n\nThis agreement incorporates the accepted offer terms and the recorded payment, insurance and handover obligations.');
