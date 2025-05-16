-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id int(11) NOT NULL AUTO_INCREMENT,
    users_id varchar(20) NOT NULL,
    message text NOT NULL,
    is_read tinyint(1) DEFAULT 0,
    created_at timestamp NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (id),
    KEY users_id (users_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4; 