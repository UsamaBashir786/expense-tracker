<?php
// session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$user_id = getCurrentUserId();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker • Smart Money Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        border: "hsl(var(--border))",
                        input: "hsl(var(--input))",
                        ring: "hsl(var(--ring))",
                        background: "hsl(var(--background))",
                        foreground: "hsl(var(--foreground))",
                        primary: {
                            DEFAULT: "hsl(var(--primary))",
                            foreground: "hsl(var(--primary-foreground))",
                        },
                        secondary: {
                            DEFAULT: "hsl(var(--secondary))",
                            foreground: "hsl(var(--secondary-foreground))",
                        },
                        destructive: {
                            DEFAULT: "hsl(var(--destructive))",
                            foreground: "hsl(var(--destructive-foreground))",
                        },
                        muted: {
                            DEFAULT: "hsl(var(--muted))",
                            foreground: "hsl(var(--muted-foreground))",
                        },
                        accent: {
                            DEFAULT: "hsl(var(--accent))",
                            foreground: "hsl(var(--accent-foreground))",
                        },
                        card: {
                            DEFAULT: "hsl(var(--card))",
                            foreground: "hsl(var(--card-foreground))",
                        },
                    },
                    borderRadius: {
                        lg: "var(--radius)",
                        md: "calc(var(--radius) - 2px)",
                        sm: "calc(var(--radius) - 4px)",
                    },
                    animation: {
                        "accordion-down": "accordion-down 0.2s ease-out",
                        "accordion-up": "accordion-up 0.2s ease-out",
                        "shimmer": "shimmer 2s linear infinite",
                    },
                    keyframes: {
                        "accordion-down": {
                            from: { height: "0" },
                            to: { height: "var(--radix-accordion-content-height)" },
                        },
                        "accordion-up": {
                            from: { height: "var(--radix-accordion-content-height)" },
                            to: { height: "0" },
                        },
                        "shimmer": {
                            "100%": { transform: "translateX(100%)" },
                        },
                    },
                },
            },
        }
    </script>
    <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>
<body class="min-h-screen bg-background text-foreground">
    
    <!-- Header - Mobile Responsive -->
    <header class="glass-header sticky top-0 z-50 w-full border-b">
        <div class="container mx-auto px-3 sm:px-4">
            <div class="flex h-14 sm:h-16 items-center justify-between">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="flex h-8 w-8 sm:h-10 sm:w-10 items-center justify-center rounded-lg bg-primary/10">
                        <i data-lucide="wallet" class="h-4 w-4 sm:h-5 sm:w-5 text-primary"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-lg sm:text-xl font-semibold truncate">Expense Tracker</h1>
                        <p class="text-xs sm:text-sm text-muted-foreground truncate">Smart money management</p>
                    </div>
                </div>
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="hidden sm:flex items-center gap-2 rounded-lg bg-muted px-3 py-2">
                        <i data-lucide="calendar" class="h-4 w-4 text-muted-foreground"></i>
                        <span class="text-sm font-medium"><?php echo date('M d, Y'); ?></span>
                    </div>
                    <button onclick="openModal()" class="btn btn-primary btn-sm sm:btn-md gap-1 sm:gap-2">
                        <i data-lucide="plus" class="h-3 w-3 sm:h-4 sm:w-4"></i>
                        <span class="hidden sm:inline">Add Expense</span>
                        <span class="sm:hidden">Add</span>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="container mx-auto px-3 sm:px-4 py-4 sm:py-8">
        <!-- Stats Grid - Mobile Responsive -->
        <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6 mb-6 sm:mb-8" id="statsContainer">
            <!-- Stats will be loaded via AJAX -->
            <?php for($i = 0; $i < 4; $i++): ?>
            <div class="card card-hover p-3 sm:p-6">
                <div class="skeleton h-3 sm:h-4 w-16 sm:w-24 mb-1 sm:mb-2"></div>
                <div class="skeleton h-6 sm:h-8 w-24 sm:w-32"></div>
            </div>
            <?php endfor; ?>
        </div>

        <!-- Main Content Grid - Mobile Responsive -->
        <div class="grid gap-4 sm:gap-6 lg:grid-cols-3">
            <!-- Left Column - Charts -->
            <div class="lg:col-span-2 space-y-4 sm:space-y-6">
                <!-- Expense Trend Chart - Mobile Responsive -->
                <div class="card p-4 sm:p-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 sm:gap-0 mb-4 sm:mb-6">
                        <div class="flex items-center gap-2">
                            <i data-lucide="trending-up" class="h-4 w-4 sm:h-5 sm:w-5 text-primary"></i>
                            <h2 class="text-base sm:text-lg font-semibold">Expense Trend</h2>
                        </div>
                        <div class="flex items-center gap-2">
                            <select id="trendPeriod" class="select text-sm w-full sm:w-40" onchange="updateCharts()">
                                <option value="7">Last 7 days</option>
                                <option value="30" selected>Last 30 days</option>
                                <option value="90">Last 90 days</option>
                            </select>
                            <button onclick="updateCharts()" class="btn btn-ghost btn-sm">
                                <i data-lucide="refresh-cw" class="h-3 w-3 sm:h-4 sm:w-4"></i>
                            </button>
                        </div>
                    </div>
                    <div class="chart-container" style="height: 250px sm:h-64 md:h-72 lg:h-80 xl:h-96">
                        <canvas id="trendChart"></canvas>
                        <div id="trendChartLoader" class="chart-loader hidden">
                            <div class="text-center">
                                <div class="loading-spinner mx-auto"></div>
                                <p class="mt-2 text-sm text-muted-foreground">Loading chart data...</p>
                            </div>
                        </div>
                    </div>
                    <div id="trendChartError" class="hidden text-center py-4 sm:py-8">
                        <p class="text-sm sm:text-base text-destructive">Failed to load trend data</p>
                        <button onclick="updateCharts()" class="btn btn-outline btn-sm sm:btn-md mt-2">
                            <i data-lucide="refresh-cw" class="h-3 w-3 sm:h-4 sm:w-4 mr-1"></i>
                            Try Again
                        </button>
                    </div>
                </div>

                <!-- Category Breakdown - Mobile Responsive -->
                <div class="card p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4 sm:mb-6">
                        <div class="flex items-center gap-2">
                            <i data-lucide="pie-chart" class="h-4 w-4 sm:h-5 sm:w-5 text-primary"></i>
                            <h2 class="text-base sm:text-lg font-semibold">Spending by Category</h2>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row items-stretch gap-4 sm:gap-6">
                        <div class="w-full md:w-1/2">
                            <div class="chart-container" style="height: 200px sm:h-56 md:h-64">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                        <div class="w-full md:w-1/2">
                            <div class="space-y-2 sm:space-y-3 max-h-64 overflow-y-auto pr-2" id="categoryLegend">
                                <?php for($i = 0; $i < 4; $i++): ?>
                                <div class="skeleton h-10 sm:h-12 w-full"></div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Recent Expenses - Mobile Responsive -->
            <div class="space-y-4 sm:space-y-6">
                <!-- Recent Expenses -->
                <div class="card p-4 sm:p-6">
                    <div class="flex items-center justify-between mb-4 sm:mb-6">
                        <div class="flex items-center gap-2">
                            <i data-lucide="list-ordered" class="h-4 w-4 sm:h-5 sm:w-5 text-primary"></i>
                            <h2 class="text-base sm:text-lg font-semibold">Recent Expenses</h2>
                        </div>
                        <div class="flex items-center gap-1 sm:gap-2">
                            <button onclick="exportData()" class="btn btn-ghost btn-xs sm:btn-sm gap-1">
                                <i data-lucide="download" class="h-3 w-3 sm:h-4 sm:w-4"></i>
                                <span class="hidden sm:inline">Export</span>
                            </button>
                        </div>
                    </div>
                    <div id="recentExpenses" class="space-y-2 sm:space-y-3 max-h-[400px] sm:max-h-[500px] overflow-y-auto pr-1 sm:pr-2">
                        <?php for($i = 0; $i < 3; $i++): ?>
                        <div class="flex items-center justify-between p-2 sm:p-3 rounded-lg border">
                            <div class="skeleton h-3 sm:h-4 w-20 sm:w-24"></div>
                            <div class="skeleton h-3 sm:h-4 w-12 sm:w-16"></div>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Quick Insights - Mobile Responsive -->
                <div class="card p-4 sm:p-6">
                    <h3 class="font-semibold text-sm sm:text-base mb-3 sm:mb-4 flex items-center gap-2">
                        <i data-lucide="zap" class="h-3 w-3 sm:h-4 sm:w-4 text-primary"></i>
                        Quick Insights
                    </h3>
                    <div class="space-y-3 sm:space-y-4">
                        <div class="flex items-center justify-between">
                            <span class="text-xs sm:text-sm text-muted-foreground">Daily Average</span>
                            <span class="text-xs sm:text-sm font-medium" id="dailyAvg">-</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs sm:text-sm text-muted-foreground">Highest Spending Day</span>
                            <span class="text-xs sm:text-sm font-medium truncate pl-2" id="highestDay">-</span>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-xs sm:text-sm text-muted-foreground">Savings Rate</span>
                            <span class="text-xs sm:text-sm font-medium" id="savingsRate">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action Buttons - Mobile Responsive -->
        <div class="mt-6 sm:mt-8 flex flex-wrap gap-2 sm:gap-4 justify-center">
            <button onclick="downloadPDF()" class="btn btn-primary btn-sm sm:btn-md gap-1 sm:gap-2 w-full sm:w-auto">
                <i data-lucide="file-text" class="h-3 w-3 sm:h-4 sm:w-4"></i>
                <span class="text-xs sm:text-sm">Download PDF Report</span>
            </button>
            <button onclick="backupData()" class="btn btn-outline btn-sm sm:btn-md gap-1 sm:gap-2 w-full sm:w-auto">
                <i data-lucide="database-backup" class="h-3 w-3 sm:h-4 sm:w-4"></i>
                <span class="text-xs sm:text-sm">Backup Data</span>
            </button>
        </div>
    </main>

    <!-- Add Expense Modal - Mobile Responsive -->
    <div id="expenseModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-3 sm:p-4">
        <div class="bg-background rounded-lg shadow-lg w-full max-w-md mx-auto animate-scale-in border max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between p-4 sm:p-6 border-b sticky top-0 bg-background">
                <div class="flex items-center gap-2">
                    <i data-lucide="plus-circle" class="h-4 w-4 sm:h-5 sm:w-5 text-primary"></i>
                    <h3 class="text-base sm:text-lg font-semibold">Add New Expense</h3>
                </div>
                <button onclick="closeModal()" class="btn btn-ghost btn-sm">
                    <i data-lucide="x" class="h-4 w-4"></i>
                </button>
            </div>
            <form id="expenseForm" class="p-4 sm:p-6 space-y-3 sm:space-y-4" onsubmit="return addExpense(event)">
                <div class="space-y-1 sm:space-y-2">
                    <label class="text-xs sm:text-sm font-medium">Amount (Rs)</label>
                    <div class="relative">
                        <i data-lucide="rupee" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground"></i>
                        <input type="number" id="amount" required min="1" step="0.01" 
                               class="input pl-8 sm:pl-10 text-sm sm:text-base" placeholder="0.00">
                    </div>
                </div>

                <div class="space-y-1 sm:space-y-2">
                    <label class="text-xs sm:text-sm font-medium">Category</label>
                    <select id="category" required class="select text-sm sm:text-base">
                        <option value="">Select category</option>
                        <?php
                        $conn = getDBConnection();
                        $result = $conn->query("SELECT * FROM categories ORDER BY name");
                        if ($result->num_rows > 0) {
                            while ($row = $result->fetch_assoc()) {
                                $icon = htmlspecialchars($row['icon']);
                                $name = htmlspecialchars($row['name']);
                                echo "<option value='{$row['id']}'>{$icon} {$name}</option>";
                            }
                        } else {
                            echo "<option value=''>No categories found</option>";
                        }
                        $conn->close();
                        ?>
                    </select>
                </div>

                <div class="space-y-1 sm:space-y-2">
                    <label class="text-xs sm:text-sm font-medium">Description</label>
                    <div class="relative">
                        <i data-lucide="file-text" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground"></i>
                        <input type="text" id="description" required 
                               class="input pl-8 sm:pl-10 text-sm sm:text-base" placeholder="What did you spend on?">
                    </div>
                </div>

                <div class="space-y-1 sm:space-y-2">
                    <label class="text-xs sm:text-sm font-medium">Date</label>
                    <div class="relative">
                        <i data-lucide="calendar" class="absolute left-3 top-1/2 transform -translate-y-1/2 h-3 w-3 sm:h-4 sm:w-4 text-muted-foreground"></i>
                        <input type="date" id="date" required 
                               class="input pl-8 sm:pl-10 text-sm sm:text-base" value="<?php echo date('Y-m-d'); ?>">
                    </div>
                </div>

                <div class="flex flex-col sm:flex-row gap-2 sm:gap-3 pt-3 sm:pt-4">
                    <button type="submit" class="btn btn-primary btn-sm sm:btn-md flex-1 gap-1 sm:gap-2 order-2 sm:order-1">
                        <i data-lucide="check" class="h-3 w-3 sm:h-4 sm:w-4"></i>
                        Add Expense
                    </button>
                    <button type="button" onclick="closeModal()" class="btn btn-outline btn-sm sm:btn-md order-1 sm:order-2">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Mobile Bottom Navigation -->
    <div class="sm:hidden fixed bottom-0 left-0 right-0 bg-background border-t z-40">
        <div class="flex items-center justify-around h-14">
            <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="flex flex-col items-center justify-center">
                <i data-lucide="home" class="h-5 w-5 text-muted-foreground"></i>
                <span class="text-xs mt-1">Home</span>
            </button>
            <button onclick="openModal()" class="flex flex-col items-center justify-center">
                <div class="bg-primary rounded-full p-3 -mt-4 shadow-lg">
                    <i data-lucide="plus" class="h-6 w-6 text-white"></i>
                </div>
                <span class="text-xs mt-1">Add</span>
            </button>
            <button onclick="document.getElementById('recentExpenses')?.scrollIntoView({behavior: 'smooth'})" class="flex flex-col items-center justify-center">
                <i data-lucide="list" class="h-5 w-5 text-muted-foreground"></i>
                <span class="text-xs mt-1">Expenses</span>
            </button>
        </div>
    </div>
<!-- Add this right before the closing </body> tag in index.php -->

<!-- Footer -->
<footer class="mt-12 sm:mt-16 pt-8 border-t">
    <div class="container mx-auto px-4">
        <!-- Mobile View -->
        <div class="sm:hidden space-y-6">
            <!-- Brand -->
            <div class="text-center">
                <div class="flex items-center justify-center gap-2 mb-3">
                    <div class="h-6 w-6 rounded-md bg-primary/10 flex items-center justify-center">
                        <i data-lucide="wallet" class="h-3 w-3 text-primary"></i>
                    </div>
                    <span class="font-semibold">Expense Tracker</span>
                </div>
                <p class="text-xs text-muted-foreground mb-4">
                    Your financial companion for smarter spending decisions.
                </p>
                
                <!-- Mobile Download Button -->
                <button onclick="openDownloadModal()" class="btn btn-outline btn-sm w-full max-w-xs mx-auto mb-4 gap-2">
                    <i data-lucide="smartphone" class="h-3 w-3"></i>
                    Get Mobile App Proposal
                </button>
            </div>

            <!-- Quick Links -->
            <div class="grid grid-cols-2 gap-4 text-center">
                <button onclick="quickDownload()" class="text-xs text-muted-foreground hover:text-primary transition-colors py-2">
                    Quick Download
                </button>
                <button onclick="exportData()" class="text-xs text-muted-foreground hover:text-primary transition-colors py-2">
                    Export Data
                </button>
                <button onclick="backupData()" class="text-xs text-muted-foreground hover:text-primary transition-colors py-2">
                    Backup
                </button>
                <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" class="text-xs text-muted-foreground hover:text-primary transition-colors py-2">
                    Back to Top
                </button>
            </div>

            <!-- Copyright -->
            <div class="text-center pt-4 border-t">
                <p class="text-xs text-muted-foreground">
                    &copy; <?php echo date('Y'); ?> Expense Tracker Pro
                </p>
                <p class="text-xs text-muted-foreground mt-1">
                    v1.0.0 • Made with ❤️
                </p>
            </div>
        </div>

        <!-- Desktop View -->
        <div class="hidden sm:block">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <!-- Left Side -->
                <div class="flex items-center gap-4">
                    <div class="h-8 w-8 rounded-lg bg-primary/10 flex items-center justify-center">
                        <i data-lucide="wallet" class="h-4 w-4 text-primary"></i>
                    </div>
                    <div>
                        <p class="font-semibold">Expense Tracker Pro</p>
                        <p class="text-xs text-muted-foreground">Track. Analyze. Optimize.</p>
                    </div>
                </div>

                <!-- Center Links -->
                <div class="flex items-center gap-6">
                    <button onclick="openDownloadModal()" class="text-sm text-muted-foreground hover:text-primary transition-colors">
                        Get App Proposal
                    </button>
                    <button onclick="quickDownload()" class="text-sm text-muted-foreground hover:text-primary transition-colors">
                        Quick Download
                    </button>
                    <button onclick="exportData()" class="text-sm text-muted-foreground hover:text-primary transition-colors">
                        Export Data
                    </button>
                </div>

                <!-- Right Side -->
                <div class="flex items-center gap-4">
                    <button onclick="openDownloadModal()" class="btn btn-outline btn-sm gap-2">
                        <i data-lucide="smartphone" class="h-3 w-3"></i>
                        Get Mobile App
                    </button>
                    <div class="text-xs text-muted-foreground">
                        v1.0.0
                    </div>
                </div>
            </div>

            <!-- Bottom Bar -->
            <div class="mt-6 pt-6 border-t text-center">
                <p class="text-sm text-muted-foreground">
                    &copy; <?php echo date('Y'); ?> Expense Tracker Pro. All rights reserved.
                </p>
            </div>
        </div>
    </div>

    <!-- Full Page Download App Modal - Mobile Responsive -->
<div id="downloadAppModal" class="hidden fixed inset-0 bg-background z-50 overflow-y-auto">
    <!-- Mobile Header (Sticky) -->
    <div class="sticky top-0 z-10 bg-background border-b shadow-sm">
        <div class="container mx-auto px-4 py-3 sm:py-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2 sm:gap-3">
                    <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-primary/10 flex items-center justify-center flex-shrink-0">
                        <i data-lucide="smartphone" class="h-4 w-4 sm:h-5 sm:w-5 text-primary"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <h1 class="text-base sm:text-xl font-bold truncate">App Development Proposal</h1>
                        <p class="text-xs sm:text-sm text-muted-foreground truncate">Expense Tracker App</p>
                    </div>
                </div>
                <div class="flex items-center gap-1 sm:gap-2">
                    <button onclick="downloadProposal()" class="btn btn-primary btn-xs sm:btn-sm gap-1 sm:gap-2">
                        <i data-lucide="download" class="h-3 w-3 sm:h-4 sm:w-4"></i>
                        <span class="hidden sm:inline">Download</span>
                    </button>
                    <button onclick="closeDownloadModal()" class="btn btn-ghost btn-xs sm:btn-sm">
                        <i data-lucide="x" class="h-4 w-4"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Content -->
    <div class="container mx-auto px-3 sm:px-4 py-4 sm:py-8">
        <!-- Hero Section -->
        <div class="text-center mb-6 sm:mb-12">
            <h2 class="text-xl sm:text-3xl font-bold text-foreground mb-2 sm:mb-4">Custom Expense Tracker App</h2>
            <p class="text-sm sm:text-lg text-muted-foreground max-w-3xl mx-auto px-2">
                Professional mobile app development with real-time analytics
            </p>
        </div>

        <!-- Developer Profile Card - Mobile Optimized -->
        <div class="card p-4 sm:p-6 mb-6 sm:mb-8">
            <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 flex items-center gap-2">
                <i data-lucide="user" class="h-4 w-4 sm:h-5 sm:w-5 text-primary"></i>
                Developer Profile
            </h3>
            
            <div class="flex flex-col sm:flex-row items-start gap-4 sm:gap-6">
                <!-- Profile Image -->
                <div class="h-16 w-16 sm:h-24 sm:w-24 rounded-xl bg-gradient-to-br from-primary/20 to-primary/10 flex items-center justify-center mx-auto sm:mx-0">
                    <span class="text-xl sm:text-3xl font-bold text-primary">UB</span>
                </div>
                
                <!-- Profile Info -->
                <div class="flex-1 w-full">
                    <h4 class="text-lg sm:text-xl font-bold text-foreground mb-2 text-center sm:text-left">Usama Bashir</h4>
                    
                    <!-- Badges - Mobile Wrap -->
                    <div class="flex flex-wrap justify-center sm:justify-start gap-1 sm:gap-2 mb-4">
                        <span class="badge badge-primary text-xs py-1 px-2">
                            <i data-lucide="code" class="h-2 w-2 sm:h-3 sm:w-3 mr-1"></i>
                            Full Stack
                        </span>
                        <span class="badge badge-success text-xs py-1 px-2">
                            <i data-lucide="database" class="h-2 w-2 sm:h-3 sm:w-3 mr-1"></i>
                            MERN Stack
                        </span>
                        <span class="badge badge-purple text-xs py-1 px-2">
                            <i data-lucide="smartphone" class="h-2 w-2 sm:h-3 sm:w-3 mr-1"></i>
                            App Dev
                        </span>
                    </div>
                    
                    <!-- Contact Grid - Mobile Stacked -->
                    <div class="space-y-3 sm:space-y-0 sm:grid sm:grid-cols-2 gap-3">
                        <!-- WhatsApp Button -->
                        <a href="https://api.whatsapp.com/send?phone=923196977218&text=Hello%20Usama%21%20I%27m%20interested%20in%20your%20Expense%20Tracker%20app%20development%20services.%20Can%20we%20discuss%3F" 
                           target="_blank"
                           class="flex items-center gap-2 sm:gap-3 p-3 rounded-lg border hover:bg-accent/50 transition-colors cursor-pointer active:scale-95">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="phone" class="h-3 w-3 sm:h-4 sm:w-4 text-blue-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs sm:text-sm text-muted-foreground">Phone / WhatsApp</p>
                                <p class="font-semibold text-sm sm:text-base text-foreground truncate">+92 319 6977218</p>
                            </div>
                        </a>
                        
                        <!-- Email Button -->
                        <a href="mailto:usamapubg50@gmail.com?subject=Expense%20Tracker%20App%20Development&body=Hello%20Usama%2C%0A%0AI'm%20interested%20in%20your%20Expense%20Tracker%20app%20development%20services.%20Please%20contact%20me%20for%20discussion."
                           class="flex items-center gap-2 sm:gap-3 p-3 rounded-lg border hover:bg-accent/50 transition-colors active:scale-95">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="mail" class="h-3 w-3 sm:h-4 sm:w-4 text-green-600"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-xs sm:text-sm text-muted-foreground">Email</p>
                                <p class="font-semibold text-sm sm:text-base text-foreground truncate">usamapubg50@gmail.com</p>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Grid - Mobile Stacked, Desktop Side-by-Side -->
        <div class="flex flex-col lg:flex-row gap-6 sm:gap-8">
            <!-- Left Column - Features & Tech -->
            <div class="lg:w-2/3 space-y-6 sm:space-y-8">
                <!-- App Features - Mobile Compact -->
                <div class="card p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 flex items-center gap-2">
                        <i data-lucide="zap" class="h-4 w-4 sm:h-5 sm:w-5 text-primary"></i>
                        App Features
                    </h3>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <!-- Feature 1 -->
                        <div class="flex items-start gap-2 sm:gap-3 p-3 rounded-lg border hover:bg-accent/50">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-blue-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="trending-up" class="h-3 w-3 sm:h-4 sm:w-4 text-blue-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-sm sm:text-base text-foreground">Real-time Analytics</h4>
                                <p class="text-xs sm:text-sm text-muted-foreground mt-1">Live expense tracking</p>
                            </div>
                        </div>
                        
                        <!-- Feature 2 -->
                        <div class="flex items-start gap-2 sm:gap-3 p-3 rounded-lg border hover:bg-accent/50">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-green-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="cloud" class="h-3 w-3 sm:h-4 sm:w-4 text-green-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-sm sm:text-base text-foreground">Cloud Sync</h4>
                                <p class="text-xs sm:text-sm text-muted-foreground mt-1">Auto backup across devices</p>
                            </div>
                        </div>
                        
                        <!-- Feature 3 -->
                        <div class="flex items-start gap-2 sm:gap-3 p-3 rounded-lg border hover:bg-accent/50">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-purple-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="smartphone" class="h-3 w-3 sm:h-4 sm:w-4 text-purple-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-sm sm:text-base text-foreground">Multi-platform</h4>
                                <p class="text-xs sm:text-sm text-muted-foreground mt-1">Web, iOS & Android</p>
                            </div>
                        </div>
                        
                        <!-- Feature 4 -->
                        <div class="flex items-start gap-2 sm:gap-3 p-3 rounded-lg border hover:bg-accent/50">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-lg bg-orange-100 flex items-center justify-center flex-shrink-0">
                                <i data-lucide="file-text" class="h-3 w-3 sm:h-4 sm:w-4 text-orange-600"></i>
                            </div>
                            <div>
                                <h4 class="font-medium text-sm sm:text-base text-foreground">Export Reports</h4>
                                <p class="text-xs sm:text-sm text-muted-foreground mt-1">PDF, CSV, Excel export</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Technology Stack - Mobile Compact -->
                <div class="card p-4 sm:p-6">
                    <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 flex items-center gap-2">
                        <i data-lucide="cpu" class="h-4 w-4 sm:h-5 sm:w-5 text-primary"></i>
                        Technology Stack
                    </h3>
                    
                    <div class="space-y-3 sm:space-y-4">
                        <div>
                            <h4 class="font-medium mb-2 text-sm sm:text-base text-foreground">Frontend</h4>
                            <div class="flex flex-wrap gap-1 sm:gap-2">
                                <span class="badge badge-outline text-xs">React.js</span>
                                <span class="badge badge-outline text-xs">Tailwind CSS</span>
                                <span class="badge badge-outline text-xs">Chart.js</span>
                                <span class="badge badge-outline text-xs">React Native</span>
                                <span class="badge badge-outline text-xs">Flutter</span>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-medium mb-2 text-sm sm:text-base text-foreground">Backend</h4>
                            <div class="flex flex-wrap gap-1 sm:gap-2">
                                <span class="badge badge-outline text-xs">Node.js</span>
                                <span class="badge badge-outline text-xs">PHP</span>
                                <span class="badge badge-outline text-xs">MySQL</span>
                                <span class="badge badge-outline text-xs">MongoDB</span>
                                <span class="badge badge-outline text-xs">Firebase</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Pricing & CTA -->
            <div class="lg:w-1/3 space-y-6 sm:space-y-8">
                <!-- Pricing Section - Mobile Compact -->
                <div>
                    <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 flex items-center gap-2">
                        <i data-lucide="credit-card" class="h-4 w-4 sm:h-5 sm:w-5 text-primary"></i>
                        Pricing Packages
                    </h3>
                    
                    <div class="space-y-4">
                        <!-- Basic Package -->
                        <div class="card p-4 border-2">
                            <div class="text-center mb-3">
                                <span class="badge badge-primary text-xs mb-2">Basic</span>
                                <div class="space-y-1">
                                    <div class="text-xl sm:text-2xl font-bold text-foreground">Rs 75,000</div>
                                    <div class="text-xs sm:text-sm text-muted-foreground">≈ $250 USD</div>
                                </div>
                            </div>
                            
                            <ul class="space-y-1.5 mb-4">
                                <li class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="h-3 w-3 text-green-500"></i>
                                    <span class="text-xs sm:text-sm">Basic expense tracking</span>
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="h-3 w-3 text-green-500"></i>
                                    <span class="text-xs sm:text-sm">Simple charts & reports</span>
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="h-3 w-3 text-green-500"></i>
                                    <span class="text-xs sm:text-sm">Single platform</span>
                                </li>
                            </ul>
                            
                            <a href="https://api.whatsapp.com/send?phone=923196977218&text=Hello%20Usama%21%20I'm%20interested%20in%20the%20Basic%20Package%20(Rs%2075%2C000)%20for%20Expense%20Tracker%20app.%20Can%20you%20provide%20more%20details%3F"
                               target="_blank"
                               class="btn btn-outline btn-xs sm:btn-sm w-full gap-1">
                                <i data-lucide="message-circle" class="h-3 w-3"></i>
                                WhatsApp Inquiry
                            </a>
                        </div>
                        
                        <!-- Pro Package -->
                        <div class="card p-4 border-2 border-primary bg-primary/5">
                            <div class="text-center mb-3">
                                <span class="badge badge-primary text-xs mb-2">Pro (Recommended)</span>
                                <div class="space-y-1">
                                    <div class="text-xl sm:text-2xl font-bold text-foreground">Rs 150,000</div>
                                    <div class="text-xs sm:text-sm text-muted-foreground">≈ $500 USD</div>
                                </div>
                            </div>
                            
                            <ul class="space-y-1.5 mb-4">
                                <li class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="h-3 w-3 text-green-500"></i>
                                    <span class="text-xs sm:text-sm">Advanced analytics</span>
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="h-3 w-3 text-green-500"></i>
                                    <span class="text-xs sm:text-sm">Multi-platform</span>
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="h-3 w-3 text-green-500"></i>
                                    <span class="text-xs sm:text-sm">Cloud sync & backup</span>
                                </li>
                            </ul>
                            
                            <a href="https://api.whatsapp.com/send?phone=923196977218&text=Hello%20Usama%21%20I'm%20interested%20in%20the%20Pro%20Package%20(Rs%20150%2C000)%20for%20Expense%20Tracker%20app.%20Can%20we%20discuss%20the%20features%20and%20timeline%3F"
                               target="_blank"
                               class="btn btn-primary btn-xs sm:btn-sm w-full gap-1">
                                <i data-lucide="message-circle" class="h-3 w-3"></i>
                                Get Details
                            </a>
                        </div>
                        
                        <!-- Enterprise Package -->
                        <div class="card p-4 border-2 border-purple-200">
                            <div class="text-center mb-3">
                                <span class="badge badge-purple text-xs mb-2">Enterprise</span>
                                <div class="space-y-1">
                                    <div class="text-lg sm:text-xl font-bold text-foreground">Custom Pricing</div>
                                    <div class="text-xs sm:text-sm text-muted-foreground">Contact for quote</div>
                                </div>
                            </div>
                            
                            <ul class="space-y-1.5 mb-4">
                                <li class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="h-3 w-3 text-green-500"></i>
                                    <span class="text-xs sm:text-sm">Custom features</span>
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="h-3 w-3 text-green-500"></i>
                                    <span class="text-xs sm:text-sm">Admin dashboard</span>
                                </li>
                                <li class="flex items-center gap-1.5">
                                    <i data-lucide="check-circle" class="h-3 w-3 text-green-500"></i>
                                    <span class="text-xs sm:text-sm">Priority support</span>
                                </li>
                            </ul>
                            
                            <a href="https://api.whatsapp.com/send?phone=923196977218&text=Hello%20Usama%21%20I'm%20interested%20in%20the%20Enterprise%20Package%20for%20Expense%20Tracker%20app.%20I%20need%20custom%20features.%20Can%20we%20discuss%20requirements%20and%20pricing%3F"
                               target="_blank"
                               class="btn btn-purple btn-xs sm:btn-sm w-full gap-1">
                                <i data-lucide="briefcase" class="h-3 w-3"></i>
                                Discuss Needs
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Timeline - Mobile Horizontal -->
                <div class="card p-4">
                    <h3 class="text-base sm:text-lg font-semibold mb-3 sm:mb-4 flex items-center gap-2">
                        <i data-lucide="calendar" class="h-4 w-4 sm:h-5 sm:w-5 text-primary"></i>
                        Timeline
                    </h3>
                    
                    <div class="flex items-center justify-between">
                        <div class="text-center">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-1">
                                <span class="text-xs sm:text-sm font-bold text-primary">1-2</span>
                            </div>
                            <p class="text-xs font-medium">Weeks</p>
                            <p class="text-xs text-muted-foreground">Basic</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-primary/20 flex items-center justify-center mx-auto mb-1">
                                <span class="text-xs sm:text-sm font-bold text-primary">2-3</span>
                            </div>
                            <p class="text-xs font-medium">Weeks</p>
                            <p class="text-xs text-muted-foreground">Pro</p>
                        </div>
                        
                        <div class="text-center">
                            <div class="h-8 w-8 sm:h-10 sm:w-10 rounded-full bg-purple-100 flex items-center justify-center mx-auto mb-1">
                                <span class="text-xs sm:text-sm font-bold text-purple-600">Custom</span>
                            </div>
                            <p class="text-xs font-medium">Timeline</p>
                            <p class="text-xs text-muted-foreground">Enterprise</p>
                        </div>
                    </div>
                </div>

                <!-- Quick Contact CTA -->
                <div class="card p-4 bg-gradient-to-br from-primary/10 to-primary/5 border-primary/20">
                    <h3 class="text-base sm:text-lg font-semibold mb-3 text-foreground">Quick Contact</h3>
                    
                    <div class="space-y-2">
                        <a href="https://api.whatsapp.com/send?phone=923196977218&text=Hello%20Usama%21%20I%20saw%20your%20Expense%20Tracker%20app%20proposal.%20Can%20we%20schedule%20a%20quick%20call%20to%20discuss%3F"
                           target="_blank"
                           class="btn btn-success btn-xs sm:btn-sm w-full gap-1">
                            <i data-lucide="message-circle" class="h-3 w-3"></i>
                            WhatsApp Chat
                        </a>
                        
                        <a href="tel:+923196977218"
                           class="btn btn-outline btn-xs sm:btn-sm w-full gap-1">
                            <i data-lucide="phone" class="h-3 w-3"></i>
                            Call Now
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Process Section - Mobile Grid -->
        <div class="card p-4 sm:p-6 mt-6 sm:mt-8">
            <h3 class="text-base sm:text-lg font-semibold mb-4 text-center">Development Process</h3>
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-6">
                <div class="text-center">
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-2">
                        <span class="text-sm sm:text-base font-bold text-primary">1</span>
                    </div>
                    <h4 class="text-xs sm:text-sm font-semibold mb-1">Discovery</h4>
                    <p class="text-xs text-muted-foreground">Requirements & planning</p>
                </div>
                
                <div class="text-center">
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-2">
                        <span class="text-sm sm:text-base font-bold text-primary">2</span>
                    </div>
                    <h4 class="text-xs sm:text-sm font-semibold mb-1">Design</h4>
                    <p class="text-xs text-muted-foreground">UI/UX & prototyping</p>
                </div>
                
                <div class="text-center">
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-2">
                        <span class="text-sm sm:text-base font-bold text-primary">3</span>
                    </div>
                    <h4 class="text-xs sm:text-sm font-semibold mb-1">Development</h4>
                    <p class="text-xs text-muted-foreground">Coding & testing</p>
                </div>
                
                <div class="text-center">
                    <div class="h-10 w-10 sm:h-12 sm:w-12 rounded-full bg-primary/10 flex items-center justify-center mx-auto mb-2">
                        <span class="text-sm sm:text-base font-bold text-primary">4</span>
                    </div>
                    <h4 class="text-xs sm:text-sm font-semibold mb-1">Deployment</h4>
                    <p class="text-xs text-muted-foreground">Launch & support</p>
                </div>
            </div>
        </div>

        <!-- Bottom CTA - Mobile Stacked -->
        <div class="mt-6 sm:mt-8 text-center">
            <div class="flex flex-col sm:flex-row gap-2 sm:gap-4 justify-center">
                <button onclick="downloadProposal()" class="btn btn-primary btn-sm sm:btn-md gap-1 sm:gap-2 order-2 sm:order-1">
                    <i data-lucide="download" class="h-3 w-3 sm:h-4 sm:w-4"></i>
                    Download Proposal
                </button>
                
                <button onclick="closeDownloadModal()" class="btn btn-outline btn-sm sm:btn-md order-1 sm:order-2">
                    Back to App
                </button>
            </div>
            
            <p class="mt-3 text-xs sm:text-sm text-muted-foreground">
                Need custom features? Contact me for a free consultation.
            </p>
        </div>
    </div>
</div>

<script>
// Modal functions
function openDownloadModal() {
    document.getElementById('downloadAppModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
    // Reinitialize icons
    setTimeout(() => lucide.createIcons(), 100);
}

function closeDownloadModal() {
    document.getElementById('downloadAppModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
}

// Download proposal function - Mobile optimized
function downloadProposal() {
    // Create mobile-optimized HTML content
    const htmlContent = `
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Expense Tracker App Proposal - Usama Bashir</title>
    <style>
        @media (max-width: 640px) {
            .container { padding: 1rem !important; }
            .hero-title { font-size: 1.75rem !important; }
            .hero-subtitle { font-size: 1rem !important; }
            .price { font-size: 1.75rem !important; }
            .section-title { font-size: 1.25rem !important; }
            .badge { font-size: 0.75rem !important; padding: 0.25rem 0.5rem !important; }
        }
        
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.5; color: #1f2937; background: #ffffff; }
        .container { max-width: 800px; margin: 0 auto; padding: 1.5rem; }
        .header { text-align: center; padding: 2rem 0; border-bottom: 2px solid #3b82f6; margin-bottom: 2rem; }
        .hero-title { font-size: 2.25rem; font-weight: 800; color: #1e40af; margin-bottom: 0.5rem; }
        .hero-subtitle { font-size: 1.125rem; color: #6b7280; }
        .section { margin-bottom: 2.5rem; }
        .section-title { font-size: 1.5rem; font-weight: 700; color: #374151; margin-bottom: 1.5rem; padding-bottom: 0.5rem; border-bottom: 1px solid #e5e7eb; }
        .grid-2 { display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1.5rem; }
        .card { background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.5rem; padding: 1.5rem; margin: 1rem 0; }
        .profile-card { background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); }
        .price-card { text-align: center; }
        .price-card.featured { border: 2px solid #3b82f6; background: #eff6ff; }
        .price { font-size: 2rem; font-weight: 800; color: #1f2937; margin: 0.5rem 0; }
        .price-sub { font-size: 0.875rem; color: #6b7280; margin-bottom: 1rem; }
        .badge { display: inline-block; padding: 0.375rem 0.75rem; border-radius: 9999px; font-size: 0.875rem; font-weight: 600; margin: 0.125rem; }
        .badge-primary { background: #3b82f6; color: white; }
        .badge-success { background: #10b981; color: white; }
        .badge-purple { background: #8b5cf6; color: white; }
        .contact-info { display: flex; align-items: center; gap: 1rem; margin: 1rem 0; }
        .contact-icon { width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; }
        .contact-icon.phone { background: #dbeafe; color: #1d4ed8; }
        .contact-icon.email { background: #f3e8ff; color: #7c3aed; }
        .feature-list { list-style: none; }
        .feature-list li { padding: 0.5rem 0; border-bottom: 1px solid #e5e7eb; display: flex; align-items: center; gap: 0.5rem; }
        .feature-list li:last-child { border-bottom: none; }
        .footer { text-align: center; margin-top: 3rem; padding-top: 1.5rem; border-top: 1px solid #e5e7eb; color: #6b7280; font-size: 0.875rem; }
        .cta-section { background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%); color: white; padding: 2rem; border-radius: 0.5rem; text-align: center; margin: 2rem 0; }
        .whatsapp-link { color: #25D366; font-weight: 600; text-decoration: none; }
        .whatsapp-link:hover { text-decoration: underline; }
        ul { padding-left: 1.25rem; margin: 0.5rem 0; }
        li { margin: 0.25rem 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1 class="hero-title">Expense Tracker Mobile App</h1>
            <p class="hero-subtitle">Professional App Development Proposal</p>
            <p style="margin-top: 0.5rem; color: #6b7280; font-size: 0.875rem;">Generated: ${new Date().toLocaleDateString()}</p>
        </div>

        <div class="section">
            <h2 class="section-title">👨‍💻 Developer Profile</h2>
            <div class="card profile-card">
                <div style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: 1rem;">
                    <div style="width: 80px; height: 80px; border-radius: 50%; background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%); display: flex; align-items: center; justify-content: center; color: white; font-size: 1.75rem; font-weight: 700;">
                        UB
                    </div>
                    <div>
                        <h3 style="font-size: 1.5rem; font-weight: 700; color: #1e40af; margin-bottom: 0.5rem;">Usama Bashir</h3>
                        <div style="margin-bottom: 1rem;">
                            <span class="badge badge-primary">Full Stack Developer</span>
                            <span class="badge badge-success">MERN Stack</span>
                            <span class="badge badge-purple">App Development</span>
                        </div>
                    </div>
                </div>
                
                <div style="margin-top: 1.5rem;">
                    <div class="contact-info">
                        <div class="contact-icon phone">
                            📞
                        </div>
                        <div>
                            <p style="font-weight: 600; color: #374151;">Phone / WhatsApp</p>
                            <p>
                                <a href="https://api.whatsapp.com/send?phone=923196977218&text=Hello%20Usama%21%20I%27m%20interested%20in%20your%20Expense%20Tracker%20app%20development%20services.%20Can%20we%20discuss%3F" 
                                   class="whatsapp-link">
                                    +92 319 6977218
                                </a>
                            </p>
                        </div>
                    </div>
                    
                    <div class="contact-info">
                        <div class="contact-icon email">
                            📧
                        </div>
                        <div>
                            <p style="font-weight: 600; color: #374151;">Email</p>
                            <p>usamapubg50@gmail.com</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="section">
            <h2 class="section-title">💰 Pricing Packages</h2>
            <div class="grid-2">
                <div class="card price-card">
                    <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">Basic Package</h3>
                    <div class="price">Rs 75,000</div>
                    <div class="price-sub">≈ $250 USD</div>
                    <ul class="feature-list">
                        <li>✓ Basic expense tracking</li>
                        <li>✓ Simple charts & reports</li>
                        <li>✓ Single platform (Web)</li>
                        <li>✓ 2 weeks development</li>
                        <li>✓ 1 month support</li>
                    </ul>
                </div>
                
                <div class="card price-card featured">
                    <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem;">Pro Package</h3>
                    <div class="price">Rs 150,000</div>
                    <div class="price-sub">≈ $500 USD</div>
                    <ul class="feature-list">
                        <li>✓ Advanced analytics</li>
                        <li>✓ Multi-platform support</li>
                        <li>✓ Cloud sync & backup</li>
                        <li>✓ 3-4 weeks development</li>
                        <li>✓ 3 months support</li>
                    </ul>
                </div>
            </div>
            
            <div class="card">
                <h3 style="font-size: 1.25rem; font-weight: 600; margin-bottom: 0.5rem; text-align: center;">Enterprise Package</h3>
                <div style="text-align: center;">
                    <div style="font-size: 1.5rem; font-weight: 800; color: #1f2937; margin: 0.5rem 0;">Custom Pricing</div>
                    <div style="color: #6b7280; margin-bottom: 1rem;">Contact for detailed quote</div>
                </div>
                <ul class="feature-list">
                    <li>✓ Custom features & branding</li>
                    <li>✓ Admin dashboard</li>
                    <li>✓ Multi-user support</li>
                    <li>✓ Priority support</li>
                    <li>✓ Extended maintenance</li>
                </ul>
            </div>
        </div>

        <div class="cta-section">
            <h2 style="font-size: 1.5rem; margin-bottom: 1rem;">Ready to Start Your Project?</h2>
            <p style="margin-bottom: 1.5rem;">Contact me today for a free consultation</p>
            <div>
                <a href="https://api.whatsapp.com/send?phone=923196977218&text=Hello%20Usama%21%20I%27m%20interested%20in%20your%20Expense%20Tracker%20app%20development%20services.%20Can%20we%20discuss%3F" 
                   style="padding: 0.75rem 1.5rem; background: #25D366; color: white; border-radius: 0.25rem; font-weight: 600; text-decoration: none; display: inline-block;">
                    💬 WhatsApp: +92 319 6977218
                </a>
                <br>
                <a href="mailto:usamapubg50@gmail.com" 
                   style="padding: 0.75rem 1.5rem; background: #EA4335; color: white; border-radius: 0.25rem; font-weight: 600; text-decoration: none; display: inline-block; margin-top: 0.75rem;">
                    📧 Email: usamapubg50@gmail.com
                </a>
            </div>
        </div>

        <div class="footer">
            <p>© ${new Date().getFullYear()} Usama Bashir - Full Stack Developer</p>
            <p style="margin-top: 0.5rem;">Generated from Expense Tracker Pro application</p>
            <p style="margin-top: 0.5rem; font-size: 0.75rem; color: #9ca3af;">
                This proposal is valid for 30 days
            </p>
        </div>
    </div>
</body>
</html>
    `;
    
    // Create download link
    const blob = new Blob([htmlContent], { type: 'text/html' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'Expense_Tracker_Proposal_Usama_Bashir.html';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
    
    showNotification('Proposal downloaded successfully!', 'success');
}

// Notification function
function showNotification(message, type = 'success') {
    const existing = document.querySelector('.notification');
    if (existing) existing.remove();
    
    const notif = document.createElement('div');
    notif.className = `notification fixed top-4 right-4 z-50 flex items-center gap-3 p-4 rounded-lg border shadow-lg animate-fade-in ${
        type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' :
        type === 'error' ? 'bg-destructive/10 border-destructive/20 text-destructive' :
        'bg-blue-50 border-blue-200 text-blue-800'
    }`;
    
    notif.innerHTML = `
        <i data-lucide="${type === 'success' ? 'check-circle' : type === 'error' ? 'alert-circle' : 'info'}" 
           class="h-5 w-5"></i>
        <span class="text-sm font-medium">${message}</span>
        <button onclick="this.parentElement.remove()" class="btn btn-ghost btn-xs ml-auto">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    `;
    
    document.body.appendChild(notif);
    lucide.createIcons();
    
    setTimeout(() => {
        if (notif.parentElement) notif.remove();
    }, 5000);
}

// Close modal on ESC key
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !document.getElementById('downloadAppModal').classList.contains('hidden')) {
        closeDownloadModal();
    }
});

// Close modal when clicking outside content on mobile
document.getElementById('downloadAppModal')?.addEventListener('click', function(e) {
    if (e.target === this && window.innerWidth < 768) {
        closeDownloadModal();
    }
});

// Initialize icons
if (document.getElementById('downloadAppModal') && !document.getElementById('downloadAppModal').classList.contains('hidden')) {
    lucide.createIcons();
}
</script>

<!-- Mobile Responsive Styles -->
<style>
/* Mobile-first responsive design */
@media (max-width: 640px) {
    #downloadAppModal {
        font-size: 14px;
    }
    
    .card {
        padding: 1rem !important;
    }
    
    h1, h2, h3, h4 {
        font-size: 90% !important;
    }
    
    .btn {
        padding: 0.375rem 0.75rem !important;
        font-size: 0.75rem !important;
    }
    
    .badge {
        font-size: 0.625rem !important;
        padding: 0.125rem 0.375rem !important;
    }
}

/* Touch-friendly buttons on mobile */
@media (hover: none) and (pointer: coarse) {
    .btn, a[href] {
        min-height: 44px !important;
        min-width: 44px !important;
    }
    
    input, textarea, select {
        font-size: 16px !important; /* Prevents zoom on iOS */
    }
}

/* Smooth scrolling for mobile */
@media (max-width: 768px) {
    #downloadAppModal {
        -webkit-overflow-scrolling: touch;
    }
}

/* Purple button styles */
.btn-purple {
    background-color: #8b5cf6;
    color: white;
    border-color: #8b5cf6;
}
.btn-purple:hover {
    background-color: #7c3aed;
    border-color: #7c3aed;
}
.badge-purple {
    background-color: #8b5cf6;
    color: white;
}

/* Active state for mobile */
@media (max-width: 768px) {
    .btn:active, a[href]:active {
        transform: scale(0.98);
        transition: transform 0.1s;
    }
}
</style>
    <!-- Back to Top Button -->
    <button onclick="window.scrollTo({top: 0, behavior: 'smooth'})" 
            class="fixed bottom-20 right-4 sm:bottom-6 sm:right-6 bg-primary text-white p-3 rounded-full shadow-lg hover:shadow-xl transition-shadow hidden z-40" 
            id="backToTop">
        <i data-lucide="chevron-up" class="h-5 w-5"></i>
    </button>
</footer>

<script>
// Download App Modal Functions
function openDownloadModal() {
    document.getElementById('downloadAppModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDownloadModal() {
    document.getElementById('downloadAppModal').classList.add('hidden');
    document.body.style.overflow = 'auto';
    document.getElementById('downloadForm').reset();
}

// Quick download without form
function quickDownload() {
    if (confirm('Download mobile app development proposal? This includes contact details for custom app development.')) {
        window.location.href = 'api/download_app.php';
    }
}

// Form submission with data collection
async function submitDownloadForm(event) {
    event.preventDefault();
    
    const formData = {
        name: document.getElementById('clientName').value,
        email: document.getElementById('clientEmail').value,
        phone: document.getElementById('clientPhone').value,
        company: document.getElementById('clientCompany').value,
        requirements: document.getElementById('clientRequirements').value,
        timestamp: new Date().toISOString()
    };
    
    try {
        // Store lead in database
        const response = await fetch('api/store_lead.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            // Close modal and initiate download
            closeDownloadModal();
            
            // Show success message
            showNotification('Thank you! Downloading proposal...');
            
            // Start download after 1 second
            setTimeout(() => {
                window.location.href = 'api/download_app.php';
            }, 1000);
            
        } else {
            showNotification('Error saving your information. Please try again.', 'error');
        }
        
    } catch (error) {
        console.error('Error:', error);
        // Fallback: direct download
        closeDownloadModal();
        setTimeout(() => {
            window.location.href = 'api/download_app.php';
        }, 500);
    }
    
    return false;
}

// Back to Top Button Functionality
window.addEventListener('scroll', () => {
    const backToTop = document.getElementById('backToTop');
    if (window.scrollY > 300) {
        backToTop.classList.remove('hidden');
    } else {
        backToTop.classList.add('hidden');
    }
});

// Notification function
function showNotification(message, type = 'success') {
    const notif = document.createElement('div');
    notif.className = `fixed top-4 right-4 z-50 flex items-center gap-3 p-4 rounded-lg border shadow-lg animate-fade-in ${
        type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' :
        'bg-destructive/10 border-destructive/20 text-destructive'
    }`;
    
    notif.innerHTML = `
        <i data-lucide="${type === 'success' ? 'check-circle' : 'alert-circle'}" 
           class="h-5 w-5"></i>
        <span class="text-sm font-medium">${message}</span>
        <button onclick="this.parentElement.remove()" class="btn btn-ghost btn-xs ml-auto">
            <i data-lucide="x" class="h-4 w-4"></i>
        </button>
    `;
    
    document.body.appendChild(notif);
    lucide.createIcons();
    
    setTimeout(() => {
        if (notif.parentElement) notif.remove();
    }, 5000);
}

// Initialize icons
lucide.createIcons();
</script>

    <script src="/assets/js/main.js"></script>
    <script>
        // Initialize on page load
        document.addEventListener('DOMContentLoaded', () => {
            loadExpenses();
            loadStats();
            initCharts();
            loadQuickInsights();
        });
        
    </script>
</body>
</html>