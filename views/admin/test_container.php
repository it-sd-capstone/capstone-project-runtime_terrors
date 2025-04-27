<?php
/**
 * Admin Dashboard Test Container
 * This partial provides a UI for running tests directly from the admin dashboard
 */

// Scan the tests directory to find available tests
$testFiles = [];
$testDir = APP_ROOT . '/tests';
if (is_dir($testDir)) {
    $files = scandir($testDir);
    foreach ($files as $file) {
        if (pathinfo($file, PATHINFO_EXTENSION) === 'php') {
            $testName = pathinfo($file, PATHINFO_FILENAME);
            $testFiles[$testName] = $file;
        }
    }
}

// Organize tests by category
$testCategories = [
    'environment' => [
        'title' => 'Environment Tests',
        'description' => 'Tests that verify your environment setup and configuration.',
        'tests' => []
    ],
    'database' => [
        'title' => 'Database Tests',
        'description' => 'Tests that verify database connectivity and operations.',
        'tests' => []
    ],
    'integration' => [
        'title' => 'Integration Tests',
        'description' => 'Tests that verify integration between system components.',
        'tests' => []
    ],
    'feature' => [
        'title' => 'Feature Tests',
        'description' => 'Tests that verify specific application features.',
        'tests' => []
    ],
    'other' => [
        'title' => 'Other Tests',
        'description' => 'Miscellaneous tests.',
        'tests' => []
    ]
];

// Categorize tests based on filename patterns
foreach ($testFiles as $testName => $fileName) {
    if (strpos($testName, 'env') !== false || strpos($testName, 'environment') !== false || strpos($testName, 'config') !== false) {
        $testCategories['environment']['tests'][$testName] = $fileName;
    } elseif (strpos($testName, 'db') !== false || strpos($testName, 'database') !== false || strpos($testName, 'model') !== false) {
        $testCategories['database']['tests'][$testName] = $fileName;
    } elseif (strpos($testName, 'integration') !== false || strpos($testName, 'tech') !== false || strpos($testName, 'api') !== false) {
        $testCategories['integration']['tests'][$testName] = $fileName;
    } elseif (strpos($testName, 'feature') !== false || strpos($testName, 'controller') !== false || strpos($testName, 'view') !== false) {
        $testCategories['feature']['tests'][$testName] = $fileName;
    } else {
        $testCategories['other']['tests'][$testName] = $fileName;
    }
}
?>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5>System Tests</h5>
                <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseTests" aria-expanded="false" aria-controls="collapseTests">
                    Toggle Tests Panel
                </button>
            </div>
            <div class="collapse" id="collapseTests">
                <div class="card-body">
                    <?php if (empty($testFiles)): ?>
                        <div class="alert alert-warning">
                            No test files found in the tests directory.
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-7">
                                <h6>Available Tests</h6>
                                <div class="accordion" id="testAccordion">
                                    <?php foreach ($testCategories as $categoryKey => $category): ?>
                                        <?php if (!empty($category['tests'])): ?>
                                            <div class="accordion-item">
                                                <h2 class="accordion-header" id="heading<?= ucfirst($categoryKey) ?>">
                                                    <button class="accordion-button collapsed" type="button" 
                                                            data-bs-toggle="collapse" 
                                                            data-bs-target="#collapse<?= ucfirst($categoryKey) ?>" 
                                                            aria-expanded="false" 
                                                            aria-controls="collapse<?= ucfirst($categoryKey) ?>">
                                                        <?= $category['title'] ?> (<?= count($category['tests']) ?>)
                                                    </button>
                                                </h2>
                                                <div id="collapse<?= ucfirst($categoryKey) ?>" 
                                                     class="accordion-collapse collapse" 
                                                     aria-labelledby="heading<?= ucfirst($categoryKey) ?>" 
                                                     data-bs-parent="#testAccordion">
                                                    <div class="accordion-body">
                                                        <p class="text-muted"><?= $category['description'] ?></p>
                                                        <div class="list-group mb-3">
                                                            <?php foreach ($category['tests'] as $testName => $fileName): ?>
                                                                <button type="button" 
                                                                        class="list-group-item list-group-item-action test-item d-flex justify-content-between align-items-center"
                                                                        data-test="<?= htmlspecialchars($testName) ?>">
                                                                    <?= htmlspecialchars(ucwords(str_replace('_', ' ', $testName))) ?>
                                                                    <span class="badge test-status bg-secondary">Not Run</span>
                                                                </button>
                                                            <?php endforeach; ?>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-md-5">
                                <h6>Test Information</h6>
                                <div class="alert alert-info">
                                    <p><strong>How to use:</strong> Select a test from the list to run it. Test results will be displayed in a modal window.</p>
                                    <p><strong>Note:</strong> Some tests may take a few moments to complete.</p>
                                </div>
                                <div class="card mb-3">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Selected Test</h6>
                                    </div>
                                    <div class="card-body">
                                        <div id="selectedTestInfo">
                                            <p class="text-muted">No test selected</p>
                                        </div>
                                        <button id="runSelectedTest" class="btn btn-primary w-100 mt-2" disabled>
                                            Run Selected Test
                                        </button>
                                    </div>
                                </div>
                                <div class="card">
                                    <div class="card-header bg-light">
                                        <h6 class="mb-0">Test Summary</h6>
                                    </div>
                                    <div class="card-body">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Total Tests:</span>
                                            <span class="badge bg-secondary"><?= count($testFiles) ?></span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Passed:</span>
                                            <span class="badge bg-success" id="passedTestsCount">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <span>Failed:</span>
                                            <span class="badge bg-danger" id="failedTestsCount">0</span>
                                        </div>
                                        <div class="d-flex justify-content-between">
                                            <span>Not Run:</span>
                                            <span class="badge bg-secondary" id="notRunTestsCount"><?= count($testFiles) ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Results Modal -->
<div class="modal fade" id="testResultsModal" tabindex="-1" aria-labelledby="testResultsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="testResultsModalLabel">Test Results</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div id="testResultsContainer">
                    <div class="d-flex justify-content-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Running test...</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="downloadTestResults">Download Results</button>
                <button type="button" class="btn btn-success" id="runAllTests">Run All Tests</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedTest = null;
    const testItems = document.querySelectorAll('.test-item');
    const runButton = document.getElementById('runSelectedTest');
    const selectedTestInfo = document.getElementById('selectedTestInfo');
    const testResults = {};
    let passedTests = 0;
    let failedTests = 0;
    let notRunTests = testItems.length;
    
    // Update counters
    function updateCounters() {
        document.getElementById('passedTestsCount').textContent = passedTests;
        document.getElementById('failedTestsCount').textContent = failedTests;
        document.getElementById('notRunTestsCount').textContent = notRunTests;
    }
    
    // Handle test selection
    testItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            testItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to selected item
            this.classList.add('active');
            
            // Store selected test
            selectedTest = this.dataset.test;
            
            // Update selected test info
            selectedTestInfo.innerHTML = `
                <h5>${selectedTest.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h5>
                <p class="text-muted">Click the button below to run this test.</p>
            `;
            
            // Enable run button
            runButton.disabled = false;
        });
    });
    
    // Function to run a test and update UI
    function runTest(testName) {
        return new Promise((resolve, reject) => {
            // Find the test item element
            const testItem = Array.from(testItems).find(item => item.dataset.test === testName);
            const statusBadge = testItem.querySelector('.test-status');
            
            // Update status to running
            statusBadge.textContent = 'Running...';
            statusBadge.className = 'badge test-status bg-warning';
            
            // Run the test
            fetch('<?= base_url('index.php/admin/runTest') ?>?test=' + encodeURIComponent(testName))
                .then(response => response.text())
                .then(html => {
                    // Store test results
                    testResults[testName] = html;
                    
                    // SIMPLIFIED LOGIC:
                    // A test fails if it contains specific error indicators
                    const errorIndicators = [
                        'class="alert alert-danger"',
                        'Fatal error:',
                        'Parse error:',
                        'Uncaught Error:',
                        'Exception:',
                        'Error Running Test',
                        'Test Failed'
                    ];
                    
                    // Check if any error indicators are present
                    const failed = errorIndicators.some(indicator => html.includes(indicator));
                    const passed = !failed;
                    
                    if (passed) {
                        statusBadge.textContent = 'Passed';
                        statusBadge.className = 'badge test-status bg-success';
                        passedTests++;
                        notRunTests--;
                    } else {
                        statusBadge.textContent = 'Failed';
                        statusBadge.className = 'badge test-status bg-danger';
                        failedTests++;
                        notRunTests--;
                    }
                    
                    updateCounters();
                    resolve({ testName, html, passed });
                })
                .catch(error => {
                    statusBadge.textContent = 'Error';
                    statusBadge.className = 'badge test-status bg-danger';
                    failedTests++;
                    notRunTests--;
                    updateCounters();
                    reject(error);
                });
        });
    }
    
    // Handle run button click
    runButton.addEventListener('click', function() {
        if (!selectedTest) return;
        
        // Show modal
        const testModal = new bootstrap.Modal(document.getElementById('testResultsModal'));
        testModal.show();
        
        // Reset results container
        const resultsContainer = document.getElementById('testResultsContainer');
        resultsContainer.innerHTML = `
            <div class="d-flex justify-content-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Running test...</span>
                </div>
            </div>
        `;
        
        // Update modal title
        document.getElementById('testResultsModalLabel').textContent =
            'Running Test: ' + selectedTest.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        
        // Run the test
        runTest(selectedTest)
            .then(({ html }) => {
                resultsContainer.innerHTML = html;
                document.getElementById('testResultsModalLabel').textContent =
                    'Test Results: ' + selectedTest.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            })
            .catch(error => {
                resultsContainer.innerHTML = `
                    <div class="alert alert-danger">
                        <h4>Error Running Test</h4>
                        <p>${error.message}</p>
                    </div>
                `;
            });
    });
    
    // Handle run all tests button
    document.getElementById('runAllTests').addEventListener('click', function() {
        // Reset counters
        passedTests = 0;
        failedTests = 0;
        notRunTests = testItems.length;
        updateCounters();
        
        // Reset results container
        const resultsContainer = document.getElementById('testResultsContainer');
        resultsContainer.innerHTML = `
            <div class="progress mb-4">
                <div class="progress-bar progress-bar-striped progress-bar-animated" 
                     role="progressbar" style="width: 0%" 
                     aria-valuenow="0" aria-valuemin="0" aria-valuemax="100" id="testProgressBar">
                    0%
                </div>
            </div>
            <div id="allTestResults">                
                <h4>Running all tests...</h4>
            </div>
        `;
        
        // Update modal title
        document.getElementById('testResultsModalLabel').textContent = 'Running All Tests';
        
        // Get all test names
        const allTestNames = Array.from(testItems).map(item => item.dataset.test);
        let completedTests = 0;
        const progressBar = document.getElementById('testProgressBar');
        const allTestResultsDiv = document.getElementById('allTestResults');
        
        // Run tests sequentially
        const runAllTestsSequentially = async () => {
            const results = {
                passed: [],
                failed: []
            };
            
            for (const testName of allTestNames) {
                try {
                    // Update progress
                    const progress = Math.round((completedTests / allTestNames.length) * 100);
                    progressBar.style.width = `${progress}%`;
                    progressBar.setAttribute('aria-valuenow', progress);
                    progressBar.textContent = `${progress}%`;
                    
                    // Run test
                    const { passed, html } = await runTest(testName);
                    
                    // Store result
                    if (passed) {
                        results.passed.push({ testName, html });
                    } else {
                        results.failed.push({ testName, html });
                    }
                    
                    // Update completed count
                    completedTests++;
                } catch (error) {
                    console.error(`Error running test ${testName}:`, error);
                    results.failed.push({
                        testName,
                        html: `<div class="alert alert-danger">
                                <h4>Error Running Test: ${testName}</h4>
                                <p>${error.message}</p>
                              </div>`
                    });
                    completedTests++;
                }
            }
            
            // Update final progress
            progressBar.style.width = '100%';
            progressBar.setAttribute('aria-valuenow', 100);
            progressBar.textContent = '100%';
            progressBar.className = 'progress-bar bg-success';
            
            // Display results summary
            let resultsHtml = `
                <h4>Test Results Summary</h4>
                <div class="alert ${results.failed.length === 0 ? 'alert-success' : 'alert-warning'}">
                    <p><strong>Total Tests:</strong> ${allTestNames.length}</p>
                    <p><strong>Passed:</strong> ${results.passed.length}</p>
                    <p><strong>Failed:</strong> ${results.failed.length}</p>
                </div>
            `;
            
            // Add failed tests first (if any)
            if (results.failed.length > 0) {
                resultsHtml += `
                    <div class="card mb-4">
                        <div class="card-header bg-danger text-white">
                            <h5 class="mb-0">Failed Tests (${results.failed.length})</h5>
                        </div>
                        <div class="card-body">
                `;
                
                results.failed.forEach(({ testName, html }) => {
                    resultsHtml += `
                        <div class="mb-4">
                            <h5>${testName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}</h5>
                            <div class="test-result border rounded p-3">${html}</div>
                        </div>
                    `;
                });
                
                resultsHtml += `
                        </div>
                    </div>
                `;
            }
            
            // Add passed tests
            if (results.passed.length > 0) {
                resultsHtml += `
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h5 class="mb-0">Passed Tests (${results.passed.length})</h5>
                        </div>
                        <div class="card-body">
                            <div class="accordion" id="passedTestsAccordion">
                `;
                
                results.passed.forEach(({ testName, html }, index) => {
                    resultsHtml += `
                        <div class="accordion-item">
                            <h2 class="accordion-header" id="heading${index}">
                                <button class="accordion-button collapsed" type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#collapse${index}"
                                        aria-expanded="false"
                                        aria-controls="collapse${index}">
                                    ${testName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())}
                                </button>
                            </h2>
                            <div id="collapse${index}"
                                 class="accordion-collapse collapse"
                                 aria-labelledby="heading${index}"
                                 data-bs-parent="#passedTestsAccordion">
                                <div class="accordion-body">
                                    ${html}
                                </div>
                            </div>
                        </div>
                    `;
                });
                
                resultsHtml += `
                            </div>
                        </div>
                    </div>
                `;
            }
            
            // Update results container
            allTestResultsDiv.innerHTML = resultsHtml;
            
            // Update modal title
            document.getElementById('testResultsModalLabel').textContent = 'All Tests Completed';
        };
        
        // Start running all tests
        runAllTestsSequentially();
    });
    
    // Handle download results button
    document.getElementById('downloadTestResults').addEventListener('click', function() {
        const resultsHtml = document.getElementById('testResultsContainer').innerHTML;
        const testName = selectedTest || 'all_tests';
        
        // Create a blob with the HTML content
        const blob = new Blob([
            '<!DOCTYPE html><html><head><title>Test Results</title>' +
            '<meta charset="UTF-8">' +
            '<meta name="viewport" content="width=device-width, initial-scale=1.0">' +
            '<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">' +
            '<style>' +
            'body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }' +
            'h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }' +
            'h2 { color: #444; margin-top: 30px; border-bottom: 1px solid #eee; padding-bottom: 5px; }' +
            '.alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 10px; border-radius: 4px; }' +
            '.alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 10px; border-radius: 4px; }' +
            '.alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 10px; margin-bottom: 10px; border-radius: 4px; }' +
            '.test-result { margin-bottom: 20px; }' +
            '</style></head><body>' +
            '<h1>Test Results: ' + (selectedTest ? selectedTest.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'All Tests') + '</h1>' +
            '<div class="container">' +
            resultsHtml +
            '</div>' +
            '' +
            '</body></html>'
        ], { type: 'text/html' });
        
        // Create a download link
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = testName + '_results.html';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    });
});
</script>
