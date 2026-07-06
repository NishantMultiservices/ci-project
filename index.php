<?php
require_once 'config/database.php';

$announcements = $conn->query("SELECT * FROM announcements WHERE is_active = 1 ORDER BY created_at DESC LIMIT 3");
$exam_count = $conn->query("SELECT COUNT(*) as c FROM exams WHERE is_active = 1")->fetch_assoc()['c'];
$note_count = $conn->query("SELECT COUNT(*) as c FROM study_notes")->fetch_assoc()['c'];
$job_count = $conn->query("SELECT COUNT(*) as c FROM job_listings WHERE is_active = 1")->fetch_assoc()['c'];

$page_title = 'Home';
include 'includes/header.php';
?>

<section class="hero">
    <div class="container">
        <div class="hero-content">
            <div class="hero-text fade-up">
                <h1>Your Success <span>Starts Here</span></h1>
                <p>Practice with mock exams, download study notes, and stay updated with the latest job opportunities — all in one place.</p>
                <div class="hero-buttons">
                    <a href="exams/index.php" class="btn btn-primary btn-lg">Start Practicing</a>
                    <a href="notes/index.php" class="btn btn-outline btn-lg">Browse Notes</a>
                </div>
            </div>
            <div class="hero-image fade-up delay-2">
                <div class="hero-floating-cards">
                    <div class="float-card card-1">
                        <span class="float-icon">📝</span>
                        <div class="float-info">
                            <strong>Mock Exams</strong>
                            <small>45+ Practice Tests</small>
                        </div>
                    </div>
                    <div class="float-card card-2">
                        <span class="float-icon">📖</span>
                        <div class="float-info">
                            <strong>Study Notes</strong>
                            <small>200+ Topics Covered</small>
                        </div>
                    </div>
                    <div class="float-card card-3">
                        <span class="float-icon">💼</span>
                        <div class="float-info">
                            <strong>Job Alerts</strong>
                            <small>Latest Openings</small>
                        </div>
                    </div>
                    <div class="float-card card-4">
                        <span class="float-icon">🎫</span>
                        <div class="float-info">
                            <strong>Hall Tickets</strong>
                            <small>Download Now</small>
                        </div>
                    </div>
                    <div class="float-card card-5">
                        <span class="float-icon">🔑</span>
                        <div class="float-info">
                            <strong>Answer Keys</strong>
                            <small>Check Answers</small>
                        </div>
                    </div>
                    <div class="float-card card-6">
                        <span class="float-icon">📢</span>
                        <div class="float-info">
                            <strong>Results</strong>
                            <small>Exam Notifications</small>
                        </div>
                    </div>
                    <div class="float-card card-7">
                        <span class="float-icon">📅</span>
                        <div class="float-info">
                            <strong>Study Planner</strong>
                            <small>Smart Schedule</small>
                        </div>
                    </div>
                    <div class="float-card card-8">
                        <span class="float-icon">📊</span>
                        <div class="float-info">
                            <strong>Progress</strong>
                            <small>Track Scores</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="stats">
    <div class="container">
        <div class="stats-grid">
            <div class="stat-item">
                <div class="stat-number"><?php echo $exam_count; ?>+</div>
                <div class="stat-label">Mock Exams</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $note_count; ?>+</div>
                <div class="stat-label">Study Notes</div>
            </div>
            <div class="stat-item">
                <div class="stat-number"><?php echo $job_count; ?>+</div>
                <div class="stat-label">Active Jobs</div>
            </div>
            <div class="stat-item">
                <div class="stat-number">500+</div>
                <div class="stat-label">Happy Students</div>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php while ($ann = $announcements->fetch_assoc()): ?>
            <div class="alert alert-<?php echo $ann['type']; ?>" style="margin-bottom:16px;">
                <strong><?php echo htmlspecialchars($ann['title']); ?>:</strong>
                <?php echo htmlspecialchars($ann['content']); ?>
            </div>
        <?php endwhile; ?>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header fade-up">
            <span class="subtitle">What We Offer</span>
            <h2>Everything You Need to Succeed</h2>
            <p>Mock exams, study notes, hall tickets, answer keys, exam results, and job updates — all in one platform.</p>
        </div>
        <div class="features-grid">
            <div class="feature-card fade-up delay-1">
                <div class="feature-icon blue">📝</div>
                <h3>Mock Exams</h3>
                <p>Practice with timed mock tests across various subjects. Get instant results and detailed performance analytics.</p>
                <a href="exams/index.php" style="margin-top:16px;display:inline-block;font-weight:600;">Browse Exams →</a>
            </div>
            <div class="feature-card fade-up delay-2">
                <div class="feature-icon green">📖</div>
                <h3>Study Notes</h3>
                <p>Access and download organized study materials curated by experts. Covering all major subjects and topics.</p>
                <a href="notes/index.php" style="margin-top:16px;display:inline-block;font-weight:600;">View Notes →</a>
            </div>
            <div class="feature-card" style="background:linear-gradient(135deg,var(--warning-light),#FEF3C7);">
                <div class="feature-icon amber">🎫</div>
                <h3>Hall Tickets</h3>
                <p>Download admit cards and hall tickets for upcoming exams. Stay prepared with exam day instructions.</p>
                <a href="hall_tickets/index.php" style="margin-top:16px;display:inline-block;font-weight:600;">Download →</a>
            </div>
            <div class="feature-card" style="background:linear-gradient(135deg,var(--success-light),#D1FAE5);">
                <div class="feature-icon green">🔑</div>
                <h3>Answer Keys</h3>
                <p>Access official answer keys for recent exams. Check your answers and estimate your scores instantly.</p>
                <a href="answers/index.php" style="margin-top:16px;display:inline-block;font-weight:600;">View Keys →</a>
            </div>
            <div class="feature-card" style="background:linear-gradient(135deg,var(--info-light),#DBEAFE);">
                <div class="feature-icon blue">📢</div>
                <h3>Results & Notifications</h3>
                <p>Track exam results, admit card releases, syllabus updates, and important announcements all in one place.</p>
                <a href="results/index.php" style="margin-top:16px;display:inline-block;font-weight:600;">View Updates →</a>
            </div>
            <div class="feature-card fade-up delay-3">
                <div class="feature-icon amber">💼</div>
                <h3>Job Notifications</h3>
                <p>Stay informed with the latest job openings, application deadlines, and career opportunities in your field.</p>
                <a href="jobs/index.php" style="margin-top:16px;display:inline-block;font-weight:600;">View Jobs →</a>
            </div>
        </div>
    </div>
</section>

<section class="section" style="background: var(--white);">
    <div class="container">
        <div class="section-header fade-up">
            <span class="subtitle">Get Started</span>
            <h2>How It Works</h2>
            <p>Three simple steps to boost your exam preparation and career.</p>
        </div>
        <div class="features-grid" style="max-width:900px;margin:0 auto;">
            <div class="feature-card fade-up delay-1" style="text-align:left;">
                <div class="feature-icon blue" style="margin:0 0 16px 0;">1️⃣</div>
                <h3>Create Account</h3>
                <p>Sign up for free and set up your profile. Track your progress across all activities.</p>
            </div>
            <div class="feature-card fade-up delay-2" style="text-align:left;">
                <div class="feature-icon green" style="margin:0 0 16px 0;">2️⃣</div>
                <h3>Practice & Learn</h3>
                <p>Take mock exams, review your performance, and study with curated notes.</p>
            </div>
            <div class="feature-card fade-up delay-3" style="text-align:left;">
                <div class="feature-icon amber" style="margin:0 0 16px 0;">3️⃣</div>
                <h3>Get Hired</h3>
                <p>Browse job listings, apply on time, and track your applications.</p>
            </div>
        </div>
    </div>
</section>

<section class="section">
    <div class="container">
        <div class="section-header fade-up">
            <span class="subtitle">Testimonials</span>
            <h2>What Our Users Say</h2>
        </div>
        <div class="features-grid">
            <div class="feature-card fade-up delay-1">
                <p style="font-style:italic;font-size:0.95rem;margin-bottom:16px;">"The mock exams are incredibly helpful. I scored 92% in my actual exam thanks to StudyHub's practice tests!"</p>
                <div style="font-weight:700;">— Priya S.</div>
                <div style="font-size:0.85rem;color:var(--gray-500);">Engineering Student</div>
            </div>
            <div class="feature-card fade-up delay-2">
                <p style="font-style:italic;font-size:0.95rem;margin-bottom:16px;">"I found my dream job through the job notifications here. The deadline reminders saved me from missing applications."</p>
                <div style="font-weight:700;">— Rahul K.</div>
                <div style="font-size:0.85rem;color:var(--gray-500);">Software Developer</div>
            </div>
            <div class="feature-card fade-up delay-3">
                <p style="font-style:italic;font-size:0.95rem;margin-bottom:16px;">"The study notes are well-organized and comprehensive. Perfect for last-minute revision before exams."</p>
                <div style="font-weight:700;">— Ananya M.</div>
                <div style="font-size:0.85rem;color:var(--gray-500);">Medical Student</div>
            </div>
        </div>
    </div>
</section>

<?php include 'includes/footer.php'; ?>
