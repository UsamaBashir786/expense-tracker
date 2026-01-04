<?php
// download_app.php
session_start();

// Log download activity
function logDownload() {
    $logFile = dirname(__DIR__) . '/logs/downloads.log';
    $timestamp = date('Y-m-d H:i:s');
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown';
    $referer = $_SERVER['HTTP_REFERER'] ?? 'Direct';
    
    $logMessage = "[{$timestamp}] [IP: {$ip}] [Referer: {$referer}] [UA: {$userAgent}] Downloaded app proposal\n";
    
    // Create logs directory if it doesn't exist
    if (!is_dir(dirname($logFile))) {
        mkdir(dirname($logFile), 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND | LOCK_EX);
}

// Create the HTML file with contact details
function createProposalDocument() {
    // You can customize these details
    $contactDetails = [
        'name' => 'Your Name', // Replace with your name
        'company' => 'Your Company Name', // Replace with your company
        'email' => 'your.email@example.com', // Replace with your email
        'phone' => '+1 (123) 456-7890', // Replace with your phone
        'website' => 'www.yourwebsite.com' // Replace with your website
    ];
    
    // Create HTML content
    $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile App Development Proposal - Expense Tracker Pro</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #374151; background: #f9fafb; }
        .container { max-width: 800px; margin: 0 auto; padding: 2rem; }
        .header { text-align: center; margin-bottom: 3rem; }
        .logo { font-size: 2.5rem; margin-bottom: 1rem; color: #3b82f6; }
        h1 { font-size: 2.5rem; color: #1f2937; margin-bottom: 1rem; }
        h2 { font-size: 1.5rem; color: #374151; margin: 2rem 0 1rem; border-bottom: 2px solid #3b82f6; padding-bottom: 0.5rem; }
        h3 { font-size: 1.25rem; color: #4b5563; margin: 1.5rem 0 0.5rem; }
        .section { background: white; border-radius: 0.75rem; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .contact-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 1rem; }
        .contact-item { padding: 1rem; background: #f3f4f6; border-radius: 0.5rem; }
        .contact-label { font-weight: 600; color: #4b5563; font-size: 0.875rem; }
        .contact-value { font-size: 1.125rem; color: #1f2937; margin-top: 0.25rem; }
        .services-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; margin-top: 1rem; }
        .service-card { border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; transition: transform 0.2s; }
        .service-card:hover { transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .service-icon { font-size: 2rem; margin-bottom: 1rem; }
        .pricing-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-top: 1rem; }
        .price-card { border: 2px solid #e5e7eb; border-radius: 0.75rem; padding: 1.5rem; text-align: center; }
        .price-card.popular { border-color: #3b82f6; background: #eff6ff; }
        .price-tag { font-size: 2rem; font-weight: bold; color: #1f2937; margin: 1rem 0; }
        .cta-section { background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); color: white; padding: 3rem; border-radius: 1rem; text-align: center; margin: 3rem 0; }
        .btn { display: inline-block; padding: 0.75rem 2rem; background: white; color: #3b82f6; text-decoration: none; border-radius: 0.5rem; font-weight: 600; margin: 0.5rem; transition: all 0.2s; }
        .btn:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.15); }
        .footer { text-align: center; margin-top: 3rem; padding-top: 2rem; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 0.875rem; }
        ul { padding-left: 1.5rem; margin: 0.5rem 0; }
        li { margin: 0.25rem 0; }
        .highlight { color: #3b82f6; font-weight: 600; }
        .process-steps { display: flex; justify-content: space-between; margin: 2rem 0; flex-wrap: wrap; }
        .step { flex: 1; min-width: 150px; text-align: center; padding: 1rem; }
        .step-number { width: 40px; height: 40px; background: #3b82f6; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1rem; font-weight: bold; }
        @media print {
            .no-print { display: none; }
            body { background: white; }
            .section { box-shadow: none; border: 1px solid #e5e7eb; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="logo">📱</div>
            <h1>Mobile App Development Proposal</h1>
            <p style="font-size: 1.125rem; color: #6b7280;">Custom Expense Tracker Mobile Application</p>
        </div>

        <div class="section">
            <h2>📞 Contact Information</h2>
            <div class="contact-grid">
                <div class="contact-item">
                    <div class="contact-label">Your Name</div>
                    <div class="contact-value">{$contactDetails['name']}</div>
                </div>
                <div class="contact-item">
                    <div class="contact-label">Company</div>
                    <div class="contact-value">{$contactDetails['company']}</div>
                </div>
                <div class="contact-item">
                    <div class="contact-label">Email</div>
                    <div class="contact-value">{$contactDetails['email']}</div>
                </div>
                <div class="contact-item">
                    <div class="contact-label">Phone</div>
                    <div class="contact-value">{$contactDetails['phone']}</div>
                </div>
                <div class="contact-item">
                    <div class="contact-label">Website</div>
                    <div class="contact-value">{$contactDetails['website']}</div>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>🚀 Our Mobile App Development Services</h2>
            <div class="services-grid">
                <div class="service-card">
                    <div class="service-icon">🍎</div>
                    <h3>iOS Development</h3>
                    <ul>
                        <li>Native iOS app (Swift/SwiftUI)</li>
                        <li>App Store deployment</li>
                        <li>Apple Watch & iPad support</li>
                        <li>Push notifications</li>
                    </ul>
                </div>
                <div class="service-card">
                    <div class="service-icon">🤖</div>
                    <h3>Android Development</h3>
                    <ul>
                        <li>Native Android app (Kotlin)</li>
                        <li>Google Play Store deployment</li>
                        <li>Material Design 3</li>
                        <li>Wear OS support</li>
                    </ul>
                </div>
                <div class="service-card">
                    <div class="service-icon">🌐</div>
                    <h3>Cross-Platform</h3>
                    <ul>
                        <li>React Native / Flutter</li>
                        <li>Single codebase for both platforms</li>
                        <li>Faster development time</li>
                        <li>Cost-effective solution</li>
                    </ul>
                </div>
                <div class="service-card">
                    <div class="service-icon">⚙️</div>
                    <h3>Backend & API</h3>
                    <ul>
                        <li>RESTful API development</li>
                        <li>Cloud database setup</li>
                        <li>Real-time data sync</li>
                        <li>User authentication</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>💰 Pricing Packages</h2>
            <div class="pricing-grid">
                <div class="price-card">
                    <h3>Basic App</h3>
                    <div class="price-tag">\$2,500</div>
                    <ul>
                        <li>Single platform (iOS OR Android)</li>
                        <li>Core expense tracking</li>
                        <li>Basic charts & reports</li>
                        <li>2 weeks development</li>
                        <li>30 days support</li>
                    </ul>
                </div>
                <div class="price-card popular">
                    <h3>Pro Package</h3>
                    <div class="price-tag">\$4,500</div>
                    <ul>
                        <li>Both iOS & Android</li>
                        <li>Advanced analytics</li>
                        <li>Cloud sync & backup</li>
                        <li>1 month development</li>
                        <li>6 months support</li>
                    </ul>
                </div>
                <div class="price-card">
                    <h3>Enterprise</h3>
                    <div class="price-tag">Custom</div>
                    <ul>
                        <li>Custom features & branding</li>
                        <li>Admin dashboard</li>
                        <li>Multi-user support</li>
                        <li>Priority support</li>
                        <li>12 months maintenance</li>
                    </ul>
                </div>
            </div>
        </div>

        <div class="section">
            <h2>📋 Development Process</h2>
            <div class="process-steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <h3>Discovery</h3>
                    <p>Requirements gathering & planning</p>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <h3>Design</h3>
                    <p>UI/UX design & wireframing</p>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <h3>Development</h3>
                    <p>Coding & implementation</p>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <h3>Deployment</h3>
                    <p>Testing & app store submission</p>
                </div>
            </div>
        </div>

        <div class="cta-section">
            <h2 style="color: white; margin-bottom: 1rem;">Ready to Build Your Mobile App?</h2>
            <p style="font-size: 1.125rem; margin-bottom: 2rem;">Contact us today for a free consultation and custom quote</p>
            <div>
                <a href="mailto:{$contactDetails['email']}" class="btn">📧 Email Us Now</a>
                <a href="tel:{$contactDetails['phone']}" class="btn" style="background: transparent; border: 2px solid white; color: white;">📞 Call Now</a>
            </div>
            <p style="margin-top: 2rem; opacity: 0.9;">We typically respond within 24 hours</p>
        </div>

        <div class="section">
            <h2>✅ Why Choose Us?</h2>
            <ul style="font-size: 1.125rem;">
                <li><span class="highlight">10+ years</span> of mobile app development experience</li>
                <li><span class="highlight">50+ successful</span> apps deployed to app stores</li>
                <li><span class="highlight">95% client</span> satisfaction rate</li>
                <li><span class="highlight">Free consultation</span> and project planning</li>
                <li><span class="highlight">Agile development</span> methodology</li>
                <li><span class="highlight">Regular updates</span> and transparent communication</li>
            </ul>
        </div>

        <div class="footer">
            <p>© " . date('Y') . " {$contactDetails['company']}. All rights reserved.</p>
            <p style="margin-top: 0.5rem;">This proposal was generated by Expense Tracker Pro • Downloaded on " . date('F j, Y') . "</p>
            <p class="no-print" style="margin-top: 1rem; font-size: 0.75rem; opacity: 0.7;">This is an HTML document - You can save it as a PDF using your browser's print function (Ctrl+P)</p>
        </div>
    </div>

    <script>
        // Auto-fill date in footer
        document.addEventListener('DOMContentLoaded', function() {
            const dateElement = document.querySelector('.footer p:nth-child(2)');
            if (dateElement) {
                const now = new Date();
                const options = { year: 'numeric', month: 'long', day: 'numeric' };
                dateElement.textContent = 'This proposal was generated by Expense Tracker Pro • Downloaded on ' + now.toLocaleDateString('en-US', options);
            }
        });
    </script>
</body>
</html>
HTML;

    return $html;
}

// Main execution
try {
    // Log the download
    logDownload();
    
    // Set headers for file download
    header('Content-Type: text/html');
    header('Content-Disposition: attachment; filename="Expense_Tracker_Mobile_App_Proposal.html"');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    
    // Output the document
    echo createProposalDocument();
    
} catch (Exception $e) {
    // Fallback: Simple HTML with error handling
    $errorHtml = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Expense Tracker Mobile App Proposal</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 2rem; text-align: center; }
        .container { max-width: 600px; margin: 0 auto; }
        .contact-info { background: #f0f0f0; padding: 1rem; border-radius: 5px; margin: 2rem 0; }
    </style>
</head>
<body>
    <div class="container">
        <h1>Expense Tracker Mobile App Development Proposal</h1>
        <p>Downloaded on: " . date('F j, Y') . "</p>
        
        <div class="contact-info">
            <h2>Contact Information</h2>
            <p><strong>Name:</strong> Your Name</p>
            <p><strong>Email:</strong> your.email@example.com</p>
            <p><strong>Phone:</strong> +1 (123) 456-7890</p>
            <p><strong>Company:</strong> Your Company Name</p>
            <p><strong>Website:</strong> www.yourwebsite.com</p>
        </div>
        
        <h2>Our Services</h2>
        <ul style="text-align: left; display: inline-block;">
            <li>iOS App Development (Swift)</li>
            <li>Android App Development (Kotlin)</li>
            <li>Cross-Platform (React Native/Flutter)</li>
            <li>Backend API Development</li>
            <li>App Store Deployment</li>
        </ul>
        
        <p style="margin-top: 2rem;">
            <strong>Contact us today for a custom quote!</strong>
        </p>
    </div>
</body>
</html>
HTML;
    
    echo $errorHtml;
}
?>