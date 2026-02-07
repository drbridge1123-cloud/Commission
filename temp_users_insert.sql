
USE commission_db;

INSERT IGNORE INTO users (username, password, display_name, role, commission_rate, uses_presuit_offer) VALUES
('charb', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Charb', 'employee', 10.00, 1),
('chong', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Chong', 'employee', 7.50, 1),
('soyong', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Soyong', 'employee', 15.00, 0),
('dave', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Dave', 'employee', 15.00, 0),
('ella', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Ella', 'employee', 15.00, 0),
('jimi', '$2y$10$YgH8xQXmVxqfYZwQkXUzYO4LmqpQhVcYqmvqMxWLpNiXaRqJzJMCi', 'Jimi', 'employee', 15.00, 0);
