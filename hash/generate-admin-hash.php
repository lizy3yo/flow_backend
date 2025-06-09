<?php
$password = "admin1234"; // Replace with your desired password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

echo "Password Hash: " . $hashed_password . "\n";

//user
//$2y$10$1OUmdJoqNs2SUJHLB9llpeudG4VbYOdly938Sx0ScZltdU93Ie.T6


//INSERT INTO admins (name, email, password, role) 
//VALUES (
//    'admin', 
//    'admin@gmail.com', 
//    '$2y$10$oqReA2xl6wHcK2XpT.3n9uFHoHWcTZB/sk.nHbyBB12yq9GmwoqL6',
//    'admin'
//);

// Insert services
// INSERT INTO `services` (`admin_id`, `name`, `description`, `hours_start`, `hours_end`, `max_queues`, `address`, `location`, `email`, `phone`, `ticket_prefix`, `created_at`, `updated_at`, `is_archived`)
// VALUES
// (79, 'IT Support', 'Technical support services for hardware and software issues', '09:00:00', '17:00:00', 50, '', 'Room 201', 'techsolutions@gmail.com', '09171234567', '', NOW(), NOW(), 0),
// (79, 'Network Maintenance', 'Network setup, monitoring, and maintenance', '09:00:00', '17:00:00', 40, '', 'Room 202', 'techsolutions@gmail.com', '09171234568', '', NOW(), NOW(), 0),
// (79, 'Software Development', 'Custom software design and development services', '09:00:00', '17:00:00', 30, '', 'Room 203', 'techsolutions@gmail.com', '09171234569', '', NOW(), NOW(), 0);
