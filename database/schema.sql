-- Database: studyhub_db
-- Create database first in phpMyAdmin or via MySQL CLI:
-- CREATE DATABASE studyhub_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

DROP TABLE IF EXISTS user_answers;
DROP TABLE IF EXISTS exam_results;
DROP TABLE IF EXISTS questions;
DROP TABLE IF EXISTS saved_items;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS notification_posts;
DROP TABLE IF EXISTS exam_notifications;
DROP TABLE IF EXISTS answer_key_posts;
DROP TABLE IF EXISTS answer_keys;
DROP TABLE IF EXISTS hall_ticket_posts;
DROP TABLE IF EXISTS hall_tickets;
DROP TABLE IF EXISTS study_notes;
DROP TABLE IF EXISTS job_listings;
DROP TABLE IF EXISTS exams;
DROP TABLE IF EXISTS categories;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100),
    avatar VARCHAR(255) DEFAULT 'default.png',
    bio TEXT,
    education VARCHAR(255),
    role ENUM('user', 'admin') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    icon VARCHAR(50) DEFAULT 'folder',
    description TEXT,
    type ENUM('exam', 'note', 'job') DEFAULT 'note'
);

CREATE TABLE exams (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    category_id INT,
    duration_minutes INT NOT NULL DEFAULT 60,
    total_questions INT NOT NULL DEFAULT 0,
    passing_score INT DEFAULT 40,
    difficulty ENUM('beginner', 'intermediate', 'advanced') DEFAULT 'intermediate',
    is_free TINYINT(1) DEFAULT 1,
    price DECIMAL(10,2) DEFAULT 0.00,
    image VARCHAR(255),
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

CREATE TABLE questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    exam_id INT NOT NULL,
    question_text TEXT NOT NULL,
    option_a VARCHAR(255) NOT NULL,
    option_b VARCHAR(255) NOT NULL,
    option_c VARCHAR(255) NOT NULL,
    option_d VARCHAR(255) NOT NULL,
    correct_option CHAR(1) NOT NULL,
    explanation TEXT,
    marks INT DEFAULT 1,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
);

CREATE TABLE exam_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    exam_id INT NOT NULL,
    score INT NOT NULL DEFAULT 0,
    total_marks INT NOT NULL DEFAULT 0,
    percentage DECIMAL(5,2) DEFAULT 0.00,
    correct_count INT DEFAULT 0,
    incorrect_count INT DEFAULT 0,
    unanswered_count INT DEFAULT 0,
    time_taken INT DEFAULT 0,
    status ENUM('passed', 'failed') DEFAULT 'failed',
    completed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE
);

CREATE TABLE user_answers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    result_id INT NOT NULL,
    question_id INT NOT NULL,
    selected_option CHAR(1),
    is_correct TINYINT(1) DEFAULT 0,
    FOREIGN KEY (result_id) REFERENCES exam_results(id) ON DELETE CASCADE,
    FOREIGN KEY (question_id) REFERENCES questions(id) ON DELETE CASCADE
);

CREATE TABLE study_notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    content LONGTEXT,
    category_id INT,
    user_id INT,
    file_path VARCHAR(255),
    file_type VARCHAR(20) DEFAULT 'pdf',
    download_count INT DEFAULT 0,
    is_featured TINYINT(1) DEFAULT 0,
    is_public TINYINT(1) DEFAULT 1,
    is_free TINYINT(1) DEFAULT 1,
    price DECIMAL(10,2) DEFAULT 0.00,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type VARCHAR(20) NOT NULL,
    item_id INT NOT NULL,
    amount DECIMAL(10,2) DEFAULT 0.00,
    status ENUM('pending', 'completed', 'failed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE job_listings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    company VARCHAR(200) NOT NULL,
    company_logo VARCHAR(255),
    advertise_no VARCHAR(100),
    total_vacancy INT DEFAULT 0,
    exam_name VARCHAR(200),
    application_type ENUM('online', 'offline', 'both') DEFAULT 'online',
    description TEXT,
    requirements TEXT,
    job_location VARCHAR(255),
    salary_range VARCHAR(100),
    important_dates TEXT,
    advertisement_url VARCHAR(500),
    official_website VARCHAR(500),
    application_link VARCHAR(500),
    other_link VARCHAR(500),
    other_link_text VARCHAR(200),
    application_deadline DATE,
    application_url VARCHAR(500),
    contact_email VARCHAR(100),
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE job_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    post_no INT,
    post_name VARCHAR(255),
    vacancy INT DEFAULT 0,
    FOREIGN KEY (job_id) REFERENCES job_listings(id) ON DELETE CASCADE
);

CREATE TABLE job_qualifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    post_no INT,
    education TEXT,
    FOREIGN KEY (job_id) REFERENCES job_listings(id) ON DELETE CASCADE
);

CREATE TABLE job_fees (
    id INT AUTO_INCREMENT PRIMARY KEY,
    job_id INT NOT NULL,
    post_no INT,
    category VARCHAR(100),
    fee VARCHAR(50),
    FOREIGN KEY (job_id) REFERENCES job_listings(id) ON DELETE CASCADE
);

CREATE TABLE saved_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    item_type ENUM('exam', 'note', 'job', 'hall_ticket', 'answer_key', 'exam_notification') NOT NULL,
    item_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_save (user_id, item_type, item_id),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE announcements (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    content TEXT,
    type ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE hall_tickets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    exam_name VARCHAR(200) NOT NULL,
    description TEXT,
    exam_date DATE,
    download_url VARCHAR(500),
    organization VARCHAR(200),
    instructions TEXT,
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE answer_keys (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    exam_name VARCHAR(200) NOT NULL,
    description TEXT,
    subject VARCHAR(100),
    download_url VARCHAR(500),
    organization VARCHAR(200),
    file_type VARCHAR(20) DEFAULT 'pdf',
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE exam_notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    exam_name VARCHAR(200),
    notification_type ENUM('result', 'admit_card', 'exam_date', 'syllabus', 'other') DEFAULT 'result',
    download_url VARCHAR(500),
    organization VARCHAR(200),
    result_date DATE,
    is_active TINYINT(1) DEFAULT 1,
    is_featured TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE hall_ticket_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    hall_ticket_id INT NOT NULL,
    post_name VARCHAR(255),
    exam_time VARCHAR(100),
    download_url VARCHAR(500),
    FOREIGN KEY (hall_ticket_id) REFERENCES hall_tickets(id) ON DELETE CASCADE
);

CREATE TABLE answer_key_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    answer_key_id INT NOT NULL,
    subject VARCHAR(200),
    download_url VARCHAR(500),
    file_type VARCHAR(20) DEFAULT 'pdf',
    FOREIGN KEY (answer_key_id) REFERENCES answer_keys(id) ON DELETE CASCADE
);

CREATE TABLE notification_posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    notification_id INT NOT NULL,
    post_name VARCHAR(255),
    download_url VARCHAR(500),
    result_date DATE,
    FOREIGN KEY (notification_id) REFERENCES exam_notifications(id) ON DELETE CASCADE
);

-- Sample Admin User (password: admin123)
INSERT INTO users (username, email, password, full_name, role) VALUES
('admin', 'admin@studyhub.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Admin User', 'admin'),
('john', 'john@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'John Doe', 'user');

-- Sample Categories
INSERT INTO categories (name, slug, icon, description, type) VALUES
('Mathematics', 'mathematics', 'calculator', 'Algebra, Calculus, Geometry & more', 'exam'),
('English', 'english', 'book-open', 'Grammar, Vocabulary, Comprehension', 'exam'),
('Science', 'science', 'flask', 'Physics, Chemistry, Biology', 'exam'),
('Computer Science', 'computer-science', 'monitor', 'Programming, DBMS, Networks', 'exam'),
('Aptitude', 'aptitude', 'brain', 'Quantitative & Logical Reasoning', 'exam'),
('Mathematics Notes', 'mathematics-notes', 'calculator', 'Math study materials', 'note'),
('Science Notes', 'science-notes', 'flask', 'Science study materials', 'note'),
('Programming Notes', 'programming-notes', 'code', 'Coding & development notes', 'note'),
('Government Jobs', 'government-jobs', 'building', 'Sarkari Naukri updates', 'job'),
('IT Jobs', 'it-jobs', 'monitor', 'Software & tech job openings', 'job');

-- Sample Exams
INSERT INTO exams (title, description, category_id, duration_minutes, total_questions, passing_score, difficulty) VALUES
('Mathematics Mock Test 1', 'Test your algebra and calculus skills', 1, 60, 10, 40, 'intermediate'),
('English Grammar Test', 'Comprehensive English grammar assessment', 2, 45, 10, 40, 'beginner'),
('Science Fundamentals', 'Basic physics, chemistry & biology', 3, 60, 10, 40, 'intermediate'),
('Computer Science Basics', 'Fundamentals of computing', 4, 30, 10, 40, 'beginner'),
('Aptitude Reasoning Test', 'Logical and quantitative reasoning', 5, 45, 10, 40, 'advanced');

-- Sample Questions for Exam 1 (Mathematics)
INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, marks) VALUES
(1, 'What is the value of π (pi) to 2 decimal places?', '3.14', '3.16', '3.12', '3.18', 'A', 'π ≈ 3.14159..., rounded to 2 decimal places is 3.14', 1),
(1, 'Solve for x: 2x + 5 = 15', 'x = 5', 'x = 10', 'x = 7', 'x = 3', 'A', '2x + 5 = 15 → 2x = 10 → x = 5', 1),
(1, 'What is the derivative of x²?', '2x', 'x', '2', 'x²', 'A', 'd/dx (xⁿ) = nxⁿ⁻¹, so d/dx (x²) = 2x', 1),
(1, 'What is the area of a circle with radius 7?', '154', '144', '164', '174', 'A', 'Area = πr² = π(49) ≈ 154 sq units', 1),
(1, 'If log₂(8) = x, what is x?', '3', '2', '4', '8', 'A', 'log₂(8) = 3 because 2³ = 8', 1),
(1, 'What is the sum of angles in a triangle?', '180°', '360°', '90°', '270°', 'A', 'Sum of interior angles of any triangle = 180°', 1),
(1, 'Simplify: (x²)³', 'x⁶', 'x⁵', 'x⁸', 'x⁹', 'A', '(x²)³ = x^(2×3) = x⁶', 1),
(1, 'What is the square root of 144?', '12', '14', '10', '16', 'A', '12 × 12 = 144', 1),
(1, 'What is 15% of 200?', '30', '25', '35', '20', 'A', '15/100 × 200 = 30', 1),
(1, 'If a = 3 and b = 4, what is c in a² + b² = c²?', '5', '6', '7', '8', 'A', '3² + 4² = 9 + 16 = 25, √25 = 5', 1);

-- Sample Questions for Exam 2 (English)
INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, marks) VALUES
(2, 'Choose the correct spelling:', 'Accommodate', 'Acommodate', 'Accomodate', 'Acomodate', 'A', '"Accommodate" has double c and double m', 1),
(2, 'What is a synonym for "benevolent"?', 'Kind', 'Cruel', 'Weak', 'Strong', 'A', 'Benevolent means well-meaning and kindly', 1),
(2, 'Identify the tense: "She has been studying for two hours."', 'Present Perfect Continuous', 'Present Continuous', 'Past Perfect', 'Future Continuous', 'A', 'Has been + verb-ing = Present Perfect Continuous', 1),
(2, 'Which word is an adverb?', 'Quickly', 'Quick', 'Quicken', 'Quickness', 'A', 'Adverbs modify verbs; quickly modifies how something is done', 1),
(2, 'Choose the correct article: ___ apple a day keeps the doctor away.', 'An', 'A', 'The', 'None', 'A', '"Apple" starts with a vowel sound, so "an" is used', 1),
(2, 'What is the opposite of "optimistic"?', 'Pessimistic', 'Realistic', 'Idealistic', 'Energetic', 'A', 'Optimistic (positive outlook) ↔ Pessimistic (negative outlook)', 1),
(2, 'Which sentence is grammatically correct?', 'She goes to school every day.', 'She go to school every day.', 'She going to school every day.', 'She gone to school every day.', 'A', 'Subject-verb agreement: She (singular) + goes (singular verb)', 1),
(2, 'What does "break the ice" mean?', 'Start a conversation', 'Break something', 'Freeze water', 'End a relationship', 'A', '"Break the ice" means to initiate conversation in a social setting', 1),
(2, 'Choose the correct passive voice: "The chef cooks the meal."', 'The meal is cooked by the chef.', 'The meal was cooked by the chef.', 'The meal is cooking by the chef.', 'The meal cooks by the chef.', 'A', 'Passive: object + is/are + past participle + by subject', 1),
(2, 'Which is a compound word?', 'Sunflower', 'Happiness', 'Running', 'Quickly', 'A', 'Sunflower = sun + flower (two words combined)', 1);

-- Sample Questions for Exam 3 (Science)
INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, marks) VALUES
(3, 'What is the chemical symbol for water?', 'H₂O', 'CO₂', 'NaCl', 'O₂', 'A', 'Water = H₂O (two hydrogen + one oxygen)', 1),
(3, 'What planet is known as the Red Planet?', 'Mars', 'Venus', 'Jupiter', 'Saturn', 'A', 'Mars has a reddish appearance due to iron oxide', 1),
(3, 'What is the speed of light approximately?', '3 × 10⁸ m/s', '3 × 10⁶ m/s', '3 × 10¹⁰ m/s', '3 × 10⁴ m/s', 'A', 'Speed of light ≈ 299,792,458 m/s ≈ 3 × 10⁸ m/s', 1),
(3, 'What is the powerhouse of the cell?', 'Mitochondria', 'Nucleus', 'Ribosome', 'Golgi body', 'A', 'Mitochondria generate most of the cell\'s ATP', 1),
(3, 'Which gas do plants absorb during photosynthesis?', 'Carbon Dioxide', 'Oxygen', 'Nitrogen', 'Hydrogen', 'A', 'Plants absorb CO₂ and release O₂ during photosynthesis', 1),
(3, 'What is the atomic number of Carbon?', '6', '12', '8', '4', 'A', 'Carbon has 6 protons, atomic number = 6', 1),
(3, 'What force keeps us grounded on Earth?', 'Gravity', 'Friction', 'Magnetism', 'Inertia', 'A', 'Gravitational force pulls objects toward Earth', 1),
(3, 'What is pH of pure water?', '7', '1', '14', '0', 'A', 'Pure water is neutral with pH = 7', 1),
(3, 'Which vitamin is produced by sunlight?', 'Vitamin D', 'Vitamin C', 'Vitamin A', 'Vitamin B', 'A', 'Sunlight triggers Vitamin D synthesis in skin', 1),
(3, 'What is the SI unit of force?', 'Newton', 'Joule', 'Watt', 'Pascal', 'A', 'Force = mass × acceleration, unit = Newton (N)', 1);

-- Sample Questions for Exam 4 (Computer Science)
INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, marks) VALUES
(4, 'What does CPU stand for?', 'Central Processing Unit', 'Computer Personal Unit', 'Central Program Utility', 'Core Processing Unit', 'A', 'CPU = Central Processing Unit, the brain of the computer', 1),
(4, 'Which language is used for web styling?', 'CSS', 'HTML', 'JavaScript', 'Python', 'A', 'CSS (Cascading Style Sheets) handles visual styling', 1),
(4, 'What is an array?', 'A collection of elements', 'A type of loop', 'A function', 'A variable', 'A', 'An array stores multiple values in one variable', 1),
(4, 'What does "localhost" refer to?', 'Your own computer', 'A remote server', 'A network router', 'A DNS server', 'A', 'Localhost (127.0.0.1) is your own machine', 1),
(4, 'What is a primary key in databases?', 'Unique identifier for a record', 'A foreign key', 'An indexed column', 'A table name', 'A', 'Primary key uniquely identifies each row in a table', 1),
(4, 'What does PHP stand for?', 'PHP: Hypertext Preprocessor', 'Private Home Page', 'Personal Hosting Protocol', 'Public HTML Processor', 'A', 'PHP is a recursive acronym: PHP: Hypertext Preprocessor', 1),
(4, 'Which data structure uses FIFO?', 'Queue', 'Stack', 'Array', 'Tree', 'A', 'FIFO = First In First Out, characteristic of a queue', 1),
(4, 'What is the full form of URL?', 'Uniform Resource Locator', 'Universal Resource Link', 'Unified Resource Locator', 'Uniform Reference Link', 'A', 'URL = Uniform Resource Locator', 1),
(4, 'What is an API?', 'Application Programming Interface', 'Automated Program Interface', 'Application Process Integration', 'Advanced Programming Instruction', 'A', 'API allows different software applications to communicate', 1),
(4, 'What does SQL stand for?', 'Structured Query Language', 'Simple Query Language', 'Standard Query Logic', 'Sequential Query Language', 'A', 'SQL is used to manage and query relational databases', 1);

-- Sample Questions for Exam 5 (Aptitude)
INSERT INTO questions (exam_id, question_text, option_a, option_b, option_c, option_d, correct_option, explanation, marks) VALUES
(5, 'If all cats are mammals and some pets are cats, which is true?', 'Some pets are mammals', 'All mammals are cats', 'No pets are mammals', 'All cats are pets', 'A', 'Since some pets are cats and all cats are mammals, some pets are mammals', 1),
(5, 'What comes next in the sequence: 2, 6, 18, 54, ___?', '162', '108', '72', '216', 'A', 'Each term is multiplied by 3: 54 × 3 = 162', 1),
(5, 'A train 100m long passes a pole in 10 seconds. What is the speed?', '10 m/s', '5 m/s', '20 m/s', '15 m/s', 'A', 'Speed = Distance/Time = 100/10 = 10 m/s', 1),
(5, 'If 5 workers can build a wall in 10 days, how many workers needed to build it in 2 days?', '25', '20', '15', '30', 'A', 'Workers × Days = constant → 5×10 = x×2 → x = 25', 1),
(5, 'What is 7/8 as a percentage?', '87.5%', '85%', '90%', '82.5%', 'A', '7/8 = 0.875 = 87.5%', 1),
(5, 'In a code language, if CAT = 24 and DOG = 26, then BAT = ?', '23', '22', '24', '21', 'A', 'C=3, A=1, T=20 → 3+1+20=24; D=4, O=15, G=7 → 4+15+7=26; B=2, A=1, T=20 → 2+1+20=23', 1),
(5, 'A clock shows 3:15. What is the angle between hour and minute hand?', '7.5°', '0°', '15°', '30°', 'A', 'At 3:15, hour hand at 97.5°, minute hand at 90°, difference = 7.5°', 1),
(5, 'If REASON is coded as 1851191514, what is the code for FAULT?', '6121120', '6121125', '612112', '612120', 'A', 'Each letter → its position: F=6, A=1, U=21, L=12, T=20', 1),
(5, 'A bag has 4 red and 6 blue balls. What is probability of picking a red?', '0.4', '0.6', '0.5', '0.3', 'A', 'P(red) = 4/(4+6) = 4/10 = 0.4', 1),
(5, 'If today is Monday, what day is 100 days from now?', 'Wednesday', 'Tuesday', 'Thursday', 'Friday', 'A', '100 mod 7 = 2 → Monday + 2 = Wednesday', 1);

-- Sample Study Notes
INSERT INTO study_notes (title, description, category_id, user_id, file_path, file_type, is_featured, is_public) VALUES
('Advanced Calculus Notes', 'Comprehensive calculus notes covering derivatives and integration', 6, NULL, 'calculus_notes.pdf', 'pdf', 1, 1),
('Physics Formulas Sheet', 'All important physics formulas for competitive exams', 7, NULL, 'physics_formulas.pdf', 'pdf', 1, 1),
('Python Programming Guide', 'Complete Python programming guide for beginners', 8, NULL, 'python_guide.pdf', 'pdf', 1, 1),
('English Vocabulary Builder', '500 essential English vocabulary words', 6, NULL, 'english_vocab.pdf', 'pdf', 0, 1),
('Data Structures Handbook', 'Comprehensive data structures reference', 8, NULL, 'ds_handbook.pdf', 'pdf', 1, 1),
('Chemistry Quick Notes', 'Quick revision notes for chemistry', 7, NULL, 'chemistry_notes.pdf', 'pdf', 0, 1);

-- Sample Job Listings
INSERT INTO job_listings (id, title, company, advertise_no, total_vacancy, exam_name, application_type, description, job_location, salary_range, important_dates, advertisement_url, official_website, application_link, other_link, other_link_text, application_deadline, is_active, is_featured) VALUES
(1, 'Combined Graduate Level Examination 2026', 'Staff Selection Commission', 'SSC/CGL/2026/01', 25000, 'SSC CGL Tier I & II 2026', 'online', 'Online application is invited from eligible Indian citizens for recruitment to various Group B and Group C posts in Government of India Ministries/Departments and Organisations.', 'All India', '₹35,000 - ₹1,50,000', '{"application_start":"2026-06-01","application_end":"2026-07-15","exam_date_tier1":"2026-08-20","exam_date_tier2":"2026-10-15","fee_payment_last":"2026-07-17"}', 'https://ssc.nic.in/advt/cgl2026.pdf', 'https://ssc.nic.in', 'https://ssc.nic.in/apply/cgl2026', 'https://ssc.nic.in/help', 'Help Guide', '2026-07-15', 1, 1),
(2, 'Engineering Services Examination 2026', 'Union Public Service Commission', 'UPSC/ESE/2026/02', 842, 'UPSC Engineering Services 2026', 'online', 'Engineering Services Examination is conducted for recruitment to Indian Engineering Services under various technical departments of Government of India.', 'All India', '₹56,100 - ₹2,00,000', '{"application_start":"2026-04-15","application_end":"2026-05-15","exam_date_prelims":"2026-07-12","exam_date_mains":"2026-10-25","interview":"2027-01-15"}', 'https://upsc.gov.in/advt/ese2026.pdf', 'https://upsc.gov.in', 'https://upsc.gov.in/apply/ese2026', '', '', '2026-05-15', 1, 1),
(3, 'Junior Associate (Clerical) Recruitment 2026', 'Institute of Banking Personnel Selection', 'IBPS/CRP/CL/2026/03', 5800, 'IBPS Clerk 2026', 'online', 'IBPS invites applications for recruitment of Clerical Cadre in participating Public Sector Banks across India.', 'All India (with state-wise vacancies)', '₹19,900 - ₹45,000', '{"application_start":"2026-07-01","application_end":"2026-07-28","exam_date_prelims":"2026-09-05","exam_date_mains":"2026-11-21","result_date":"2026-12-30"}', 'https://ibps.in/advt/clerk2026.pdf', 'https://ibps.in', 'https://ibps.in/apply/clerk2026', '', '', '2026-07-28', 1, 0);

-- Sample Job Posts
INSERT INTO job_posts (job_id, post_no, post_name, vacancy) VALUES
(1, 1, 'Assistant Audit Officer', 2500),
(1, 2, 'Assistant Section Officer', 8000),
(1, 3, 'Statistical Investigator Grade II', 500),
(1, 4, 'Junior Statistical Officer', 3000),
(1, 5, 'Auditor / Accountant', 11000),
(2, 1, 'Civil Engineering', 250),
(2, 2, 'Mechanical Engineering', 220),
(2, 3, 'Electrical Engineering', 200),
(2, 4, 'Electronics & Telecommunication Engineering', 172),
(3, 1, 'Junior Associate (Clerical)', 5800);

-- Sample Job Qualifications
INSERT INTO job_qualifications (job_id, post_no, education) VALUES
(1, 1, 'Bachelor Degree in Commerce/Accounting from recognized university'),
(1, 2, 'Any Graduate from recognized university'),
(1, 3, 'PG Diploma in Statistics or Bachelor Degree with Statistics as subject'),
(1, 4, 'Bachelor Degree in Statistics/Mathematics/Economics/Commerce'),
(1, 5, 'Bachelor Degree in any discipline from recognized university'),
(2, 1, 'Bachelor Degree in Civil Engineering from recognized university/institution'),
(2, 2, 'Bachelor Degree in Mechanical Engineering from recognized university/institution'),
(2, 3, 'Bachelor Degree in Electrical Engineering from recognized university/institution'),
(2, 4, 'Bachelor Degree in Electronics & Telecommunication Engineering from recognized university/institution'),
(3, 1, 'Any Graduate with 60% marks (55% for SC/ST/PWD) and proficiency in official language of the state');

-- Sample Job Fees
INSERT INTO job_fees (job_id, post_no, category, fee) VALUES
(1, 0, 'General / OBC', '₹100'),
(1, 0, 'SC / ST / PWD', '₹0 (Exempted)'),
(1, 0, 'Women', '₹0 (Exempted)'),
(2, 0, 'General / OBC', '₹200'),
(2, 0, 'SC / ST / PWD', '₹0 (Exempted)'),
(2, 0, 'Women', '₹0 (Exempted)'),
(3, 0, 'General / OBC', '₹850'),
(3, 0, 'SC / ST / PWD', '₹175'),
(3, 0, 'Women', '₹175');

-- Sample Hall Tickets
INSERT INTO hall_tickets (title, exam_name, description, exam_date, organization, instructions, is_featured) VALUES
('GATE 2026 Hall Ticket', 'GATE 2026', 'Download your GATE 2026 admit card for examination access.', '2026-08-15', 'IIT Bombay', 'Carry a printed copy along with a valid photo ID. Reach the center 1 hour before the exam.', 1),
('SSC CGL Hall Ticket 2026', 'SSC CGL 2026', 'SSC Combined Graduate Level examination hall ticket.', '2026-09-10', 'Staff Selection Commission', 'Affix a recent passport-size photograph. Report at 8:00 AM sharp.', 1),
('NEET UG 2026 Admit Card', 'NEET UG 2026', 'National Eligibility cum Entrance Test admit card for undergraduate medical courses.', '2026-07-20', 'NTA', 'Read all instructions carefully. No electronic devices allowed inside the exam hall.', 0),
('UPSC Prelims 2026 Hall Ticket', 'UPSC Civil Services 2026', 'UPSC Preliminary examination admit card for civil services aspirants.', '2026-08-02', 'UPSC', 'Bring original photo ID along with the admit card. Entry closes 30 minutes before the exam.', 1),
('Bank PO 2026 Call Letter', 'IBPS Bank PO 2026', 'IBPS Probationary Officer preliminary exam call letter.', '2026-09-25', 'IBPS', 'Signature must match the one on your application form. No stationery items allowed.', 0);

-- Sample Answer Keys
INSERT INTO answer_keys (title, exam_name, description, subject, organization, is_featured) VALUES
('GATE 2026 Answer Key - CS', 'GATE 2026', 'Official answer key for Computer Science paper', 'Computer Science', 'IIT Bombay', 1),
('GATE 2026 Answer Key - ME', 'GATE 2026', 'Official answer key for Mechanical Engineering paper', 'Mechanical Engineering', 'IIT Bombay', 1),
('SSC CGL 2026 Tier 1 Answer Key', 'SSC CGL 2026', 'Tier 1 preliminary exam answer key with detailed solutions', 'General Awareness', 'Staff Selection Commission', 1),
('NEET UG 2026 Answer Key', 'NEET UG 2026', 'Provisional answer key for NEET UG 2026 examination', 'Physics, Chemistry, Biology', 'NTA', 0),
('UPSC Prelims 2026 Answer Key', 'UPSC Civil Services 2026', 'Preliminary exam answer key for all paper sets', 'General Studies', 'UPSC', 1),
('IBPS PO Prelims Answer Key 2026', 'IBPS Bank PO 2026', 'Preliminary exam answer key with correct answers', 'Reasoning, Quant, English', 'IBPS', 0);

-- Sample Exam Notifications
INSERT INTO exam_notifications (title, description, exam_name, notification_type, organization, result_date, is_featured) VALUES
('GATE 2026 Results Announced', 'Graduate Aptitude Test in Engineering 2026 results have been declared. Check your score card now.', 'GATE 2026', 'result', 'IIT Bombay', '2026-10-15', 1),
('SSC CGL 2026 Tier 1 Results', 'SSC CGL Tier 1 examination results are now available for download.', 'SSC CGL 2026', 'result', 'Staff Selection Commission', '2026-11-01', 1),
('NEET UG 2026 Result Declaration', 'NEET UG 2026 results will be declared on the scheduled date. Check official website.', 'NEET UG 2026', 'result', 'NTA', '2026-08-20', 1),
('UPSC Prelims 2026 Result', 'UPSC Civil Services Preliminary examination results announced. Qualifiers proceed to Mains.', 'UPSC Civil Services 2026', 'result', 'UPSC', '2026-10-05', 1),
('GATE 2026 Exam Date Announcement', 'GATE 2026 examination schedule has been released. Download your hall ticket now.', 'GATE 2026', 'exam_date', 'IIT Bombay', '2026-08-15', 0),
('UPSC Releases Revised Syllabus', 'UPSC has released the revised syllabus for Civil Services Mains examination 2026.', 'UPSC Civil Services 2026', 'syllabus', 'UPSC', NULL, 0),
('IBPS PO 2026 Result Out', 'IBPS PO preliminary exam results have been declared. Check your qualifying status.', 'IBPS Bank PO 2026', 'result', 'IBPS', '2026-10-20', 1),
('SSC Releases Admit Card for Mains', 'SSC CGL 2026 Mains examination admit card has been released for download.', 'SSC CGL 2026', 'admit_card', 'Staff Selection Commission', '2026-12-01', 0);

-- Sample Announcements
INSERT INTO announcements (title, content, type, is_active) VALUES
('Welcome to StudyHub!', 'Start your exam preparation journey with our comprehensive mock tests and study materials.', 'success', 1),
('New Exams Added', 'We have added 5 new mock exams for Mathematics and Computer Science.', 'info', 1),
('Upcoming Deadlines', 'Several job applications are closing soon. Check the Jobs section regularly.', 'warning', 1),
('Hall Tickets Available', 'Download hall tickets for GATE, SSC CGL, and other major exams from the Hall Tickets section.', 'info', 1),
('Answer Keys Released', 'Answer keys for GATE, NEET, and UPSC exams are now available in the Answer Keys section.', 'success', 1);
