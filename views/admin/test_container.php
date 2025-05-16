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
                                                        aria-labelledby="heading<?= ucfirst($categoryKey) ?>">
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

<!-- All JS in One Place -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize variables
    let selectedTest = null;
    const testItems = document.querySelectorAll('.test-item');
    const runButton = document.getElementById('runSelectedTest');
    const selectedTestInfo = document.getElementById('selectedTestInfo');
    const testResultsContainer = document.getElementById('testResultsContainer');
    const testResults = {};
    let passedTests = 0;
    let failedTests = 0;
    let notRunTests = testItems.length;
    
    // Get modal element and create Bootstrap modal instance
    const modalElement = document.getElementById('testResultsModal');
    const testModal = new bootstrap.Modal(modalElement, {
        backdrop: 'static',  // Prevent closing on outside click
        keyboard: true       // Allow ESC key to close
    });
    
    // Properly handle modal cleanup when hidden
    modalElement.addEventListener('hidden.bs.modal', function() {
        // Remove any backdrop that might be stuck
        const backdrops = document.querySelectorAll('.modal-backdrop');
        backdrops.forEach(backdrop => {
            backdrop.remove();
        });
        
        // Fix body classes
        document.body.classList.remove('modal-open');
        document.body.style.overflow = '';
        document.body.style.paddingRight = '';
    });
    
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
            selectedTestInfo.innerHTML = '';
            
            const heading = document.createElement('h5');
            heading.textContent = selectedTest.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            
            const paragraph = document.createElement('p');
            paragraph.className = 'text-muted';
            paragraph.textContent = 'Click the button below to run this test.';
            
            selectedTestInfo.appendChild(heading);
            selectedTestInfo.appendChild(paragraph);
            
            // Enable run button
            runButton.disabled = false;
        });
    });
    
    // Function to run a test and update UI
    function runTest(testName) {
        return new Promise((resolve, reject) => {
            // Find the test item element
            const testItem = Array.from(testItems).find(item => item.dataset.test === testName);
            if (!testItem) {
                reject(new Error(`Test item not found: ${testName}`));
                return;
            }
            
            const statusBadge = testItem.querySelector('.test-status');
            
            // Update status to running
            statusBadge.textContent = 'Running...';
            statusBadge.className = 'badge test-status bg-warning';
            
            // Run the test
            fetch('<?= base_url('index.php/admin/runTest') ?>?test=' + encodeURIComponent(testName))
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.text();
                })
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
                    console.error("Test error:", error);
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
        
        // Show modal and reset container
        testResultsContainer.innerHTML = '';
        
        // Create loading spinner
        const spinnerContainer = document.createElement('div');
        spinnerContainer.className = 'd-flex justify-content-center';
        
        const spinner = document.createElement('div');
        spinner.className = 'spinner-border text-primary';
        spinner.setAttribute('role', 'status');
        
        const spinnerText = document.createElement('span');
        spinnerText.className = 'visually-hidden';
        spinnerText.textContent = 'Running test...';
        
        spinner.appendChild(spinnerText);
        spinnerContainer.appendChild(spinner);
        testResultsContainer.appendChild(spinnerContainer);
        
        // Update modal title
        document.getElementById('testResultsModalLabel').textContent = 
            'Running Test: ' + selectedTest.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
        
        testModal.show();
        
        // Run the test
        runTest(selectedTest)
            .then(({ html }) => {
                // Create a container for the test output
                const container = document.createElement('div');
                container.className = 'test-results-output';
                
                // Use DOMParser to safely parse the HTML
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                
                // Append only the body content
                container.innerHTML = doc.body.innerHTML;
                
                // Clear and append the results
                testResultsContainer.innerHTML = '';
                testResultsContainer.appendChild(container);
                
                // Update modal title
                document.getElementById('testResultsModalLabel').textContent =
                    'Test Results: ' + selectedTest.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
            })
            .catch(error => {
                // Create error alert
                testResultsContainer.innerHTML = '';
                
                const errorAlert = document.createElement('div');
                errorAlert.className = 'alert alert-danger';
                
                const errorHeading = document.createElement('h4');
                errorHeading.textContent = 'Error Running Test';
                
                const errorMessage = document.createElement('p');
                errorMessage.textContent = error.message || 'Unknown error occurred';
                
                errorAlert.appendChild(errorHeading);
                errorAlert.appendChild(errorMessage);
                testResultsContainer.appendChild(errorAlert);
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
        testResultsContainer.innerHTML = '';
        
        // Create progress bar
        const progressContainer = document.createElement('div');
        progressContainer.className = 'progress mb-4';
        
        const progressBar = document.createElement('div');
        progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated';
        progressBar.setAttribute('role', 'progressbar');
        progressBar.style.width = '0%';
        progressBar.setAttribute('aria-valuenow', '0');
        progressBar.setAttribute('aria-valuemin', '0');
        progressBar.setAttribute('aria-valuemax', '100');
        progressBar.id = 'testProgressBar';
        progressBar.textContent = '0%';
        
        progressContainer.appendChild(progressBar);
        testResultsContainer.appendChild(progressContainer);
        
        // Create results area
        const allTestResultsDiv = document.createElement('div');
        allTestResultsDiv.id = 'allTestResults';
        
        const runningHeading = document.createElement('h4');
        runningHeading.textContent = 'Running all tests...';
        
        allTestResultsDiv.appendChild(runningHeading);
        testResultsContainer.appendChild(allTestResultsDiv);
        
        // Update modal title
        document.getElementById('testResultsModalLabel').textContent = 'Running All Tests';
        
        // Show modal
        testModal.show();
        
        // Get all test names
        const allTestNames = Array.from(testItems).map(item => item.dataset.test);
        let completedTests = 0;
        
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
                    
                    // Create error HTML using DOM
                    const errorDiv = document.createElement('div');
                    errorDiv.className = 'alert alert-danger';
                    
                    const errorHeading = document.createElement('h4');
                    errorHeading.textContent = `Error Running Test: ${testName}`;
                    
                    const errorParagraph = document.createElement('p');
                    errorParagraph.textContent = error.message || 'Unknown error occurred';
                    
                    errorDiv.appendChild(errorHeading);
                    errorDiv.appendChild(errorParagraph);
                    
                    // Create a dummy DOM parser to extract HTML as string
                    const tempDiv = document.createElement('div');
                    tempDiv.appendChild(errorDiv);
                    
                    results.failed.push({
                        testName,
                        html: tempDiv.innerHTML
                    });
                    
                    completedTests++;
                }
            }
            
            // Update final progress
            progressBar.style.width = '100%';
            progressBar.setAttribute('aria-valuenow', 100);
            progressBar.textContent = '100%';
            progressBar.className = 'progress-bar bg-success';
            
            // Clear results div
            allTestResultsDiv.innerHTML = '';
            
            // Create summary heading
            const summaryHeading = document.createElement('h4');
            summaryHeading.textContent = 'Test Results Summary';
            allTestResultsDiv.appendChild(summaryHeading);
            
            // Create summary alert
            const summaryAlert = document.createElement('div');
            summaryAlert.className = 'alert ' + (results.failed.length === 0 ? 'alert-success' : 'alert-warning');
            
            const totalPara = document.createElement('p');
            const totalStrong = document.createElement('strong');
            totalStrong.textContent = 'Total Tests:';
            totalPara.appendChild(totalStrong);
            totalPara.append(' ' + allTestNames.length);
            
            const passedPara = document.createElement('p');
            const passedStrong = document.createElement('strong');
            passedStrong.textContent = 'Passed:';
            passedPara.appendChild(passedStrong);
            passedPara.append(' ' + results.passed.length);
            
            const failedPara = document.createElement('p');
            const failedStrong = document.createElement('strong');
            failedStrong.textContent = 'Failed:';
            failedPara.appendChild(failedStrong);
            failedPara.append(' ' + results.failed.length);
            
            summaryAlert.appendChild(totalPara);
            summaryAlert.appendChild(passedPara);
            summaryAlert.appendChild(failedPara);
            
            allTestResultsDiv.appendChild(summaryAlert);
            
            // Add failed tests first (if any)
            if (results.failed.length > 0) {
                const failedCard = document.createElement('div');
                failedCard.className = 'card mb-4';
                
                const failedCardHeader = document.createElement('div');
                failedCardHeader.className = 'card-header bg-danger text-white';
                
                const failedCardTitle = document.createElement('h5');
                failedCardTitle.className = 'mb-0';
                failedCardTitle.textContent = `Failed Tests (${results.failed.length})`;
                
                failedCardHeader.appendChild(failedCardTitle);
                failedCard.appendChild(failedCardHeader);
                
                const failedCardBody = document.createElement('div');
                failedCardBody.className = 'card-body';
                
                results.failed.forEach(({ testName, html }, index) => {
                    const testContainer = document.createElement('div');
                    testContainer.className = 'mb-4';
                    
                    const testTitle = document.createElement('h5');
                    testTitle.textContent = testName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    
                    const testResultDiv = document.createElement('div');
                    testResultDiv.className = 'test-result border rounded p-3';
                    testResultDiv.id = `failed-test-${index}`;
                    
                    testContainer.appendChild(testTitle);
                    testContainer.appendChild(testResultDiv);
                    failedCardBody.appendChild(testContainer);
                });
                
                failedCard.appendChild(failedCardBody);
                allTestResultsDiv.appendChild(failedCard);
            }
            
            // Add passed tests
            if (results.passed.length > 0) {
                const passedCard = document.createElement('div');
                passedCard.className = 'card';
                
                const passedCardHeader = document.createElement('div');
                passedCardHeader.className = 'card-header bg-success text-white';
                
                const passedCardTitle = document.createElement('h5');
                passedCardTitle.className = 'mb-0';
                passedCardTitle.textContent = `Passed Tests (${results.passed.length})`;
                
                passedCardHeader.appendChild(passedCardTitle);
                passedCard.appendChild(passedCardHeader);
                
                const passedCardBody = document.createElement('div');
                passedCardBody.className = 'card-body';
                
                const accordion = document.createElement('div');
                accordion.className = 'accordion';
                accordion.id = 'passedTestsAccordion';
                
                results.passed.forEach(({ testName, html }, index) => {
                    const accordionItem = document.createElement('div');
                    accordionItem.className = 'accordion-item';
                    
                    const accordionHeader = document.createElement('h2');
                    accordionHeader.className = 'accordion-header';
                    accordionHeader.id = `heading${index}`;
                    
                    const accordionButton = document.createElement('button');
                    accordionButton.className = 'accordion-button collapsed';
                    accordionButton.type = 'button';
                    accordionButton.setAttribute('data-bs-toggle', 'collapse');
                    accordionButton.setAttribute('data-bs-target', `#collapse${index}`);
                    accordionButton.setAttribute('aria-expanded', 'false');
                    accordionButton.setAttribute('aria-controls', `collapse${index}`);
                    accordionButton.textContent = testName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
                    
                    accordionHeader.appendChild(accordionButton);
                    
                    const accordionCollapse = document.createElement('div');
                    accordionCollapse.id = `collapse${index}`;
                    accordionCollapse.className = 'accordion-collapse collapse';
                    accordionCollapse.setAttribute('aria-labelledby', `heading${index}`);
                    accordionCollapse.setAttribute('data-bs-parent', '#passedTestsAccordion');
                    
                    const accordionBody = document.createElement('div');
                    accordionBody.className = 'accordion-body';
                    accordionBody.id = `passed-test-${index}`;
                    
                    accordionCollapse.appendChild(accordionBody);
                    
                    accordionItem.appendChild(accordionHeader);
                    accordionItem.appendChild(accordionCollapse);
                    
                    accordion.appendChild(accordionItem);
                });
                
                passedCardBody.appendChild(accordion);
                passedCard.appendChild(passedCardBody);
                allTestResultsDiv.appendChild(passedCard);
            }
            
            // Inject HTML content safely
            results.failed.forEach(({ html }, index) => {
                const container = document.getElementById(`failed-test-${index}`);
                if (container) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    container.innerHTML = doc.body.innerHTML;
                }
            });
            
            results.passed.forEach(({ html }, index) => {
                const container = document.getElementById(`passed-test-${index}`);
                if (container) {
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    container.innerHTML = doc.body.innerHTML;
                }
            });
            
            // Update modal title
            document.getElementById('testResultsModalLabel').textContent = 'All Tests Completed';
        };
        
        // Start running all tests
        runAllTestsSequentially();
    });
    
    // Handle download results button
    document.getElementById('downloadTestResults').addEventListener('click', function() {
        const resultsHtml = testResultsContainer.innerHTML;
        const testName = selectedTest || 'all_tests';
        
        // Create document structure with DOM methods
        const docType = document.implementation.createDocumentType('html', '', '');
        const doc = document.implementation.createDocument('', 'html', docType);
        
        // Create head
        const head = doc.createElement('head');
        
        const title = doc.createElement('title');
        title.textContent = 'Test Results';
        
        const meta1 = doc.createElement('meta');
        meta1.setAttribute('charset', 'UTF-8');
        
        const meta2 = doc.createElement('meta');
        meta2.setAttribute('name', 'viewport');
        meta2.setAttribute('content', 'width=device-width, initial-scale=1.0');
        
        const link = doc.createElement('link');
        link.setAttribute('href', 'https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css');
        link.setAttribute('rel', 'stylesheet');
        
        const style = doc.createElement('style');
        style.textContent = `
            body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }
            h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }
            h2 { color: #444; margin-top: 30px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
            .alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 10px; border-radius: 4px; }
            .alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 10px; border-radius: 4px; }
            .alert-warning { background-color: #fff3cd; color: #856404; border: 1px solid #ffeeba; padding: 10px; margin-bottom: 10px; border-radius: 4px; }
            .test-result { margin-bottom: 20px; }
        `;
        
        head.appendChild(title);
        head.appendChild(meta1);
        head.appendChild(meta2);
        head.appendChild(link);
        head.appendChild(style);
        
        // Create body
        const body = doc.createElement('body');
        
        // Create main heading
        const heading = doc.createElement('h1');
        heading.textContent = 'Test Results: ' + (selectedTest ? selectedTest.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) : 'All Tests');
        
        // Create container
        const container = doc.createElement('div');
        container.className = 'container';
        
        // Add content to container
        container.innerHTML = resultsHtml;
        
        // Build document structure
        body.appendChild(heading);
        body.appendChild(container);
        
        doc.documentElement.appendChild(head);
        doc.documentElement.appendChild(body);
        
        // Serialize the document to string
        const serializer = new XMLSerializer();
        const htmlString = '<!DOCTYPE html>\n' + serializer.serializeToString(doc);
        
        // Create a blob with the HTML content
        const blob = new Blob([htmlString], { type: 'text/html' });
        
        // Create a download link
        const url = URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = testName + '_results.html';
        
        // Append to body, click, and remove
        document.body.appendChild(a);
        a.click();
        
        // Clean up
        setTimeout(() => {
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        }, 100);
    });
    
    // Extra fix for possible modal backdrop issues
    document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(button => {
        button.addEventListener('click', function() {
            // Make sure modal is properly hidden
            testModal.hide();
            
            // Manually clean up backdrop after a short delay
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                backdrops.forEach(backdrop => {
                    backdrop.remove();
                });
                
                // Reset body
                document.body.classList.remove('modal-open');
                document.body.style.overflow = '';
                document.body.style.paddingRight = '';
            }, 300);
        });
    });
    
    // Additional fix for ESC key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && document.body.classList.contains('modal-open')) {
            setTimeout(() => {
                const backdrops = document.querySelectorAll('.modal-backdrop');
                if (backdrops.length > 0) {
                    backdrops.forEach(backdrop => {
                        backdrop.remove();
                    });
                    
                    // Reset body
                    document.body.classList.remove('modal-open');
                    document.body.style.overflow = '';
                    document.body.style.paddingRight = '';
                }
            }, 300);
        }
    });
});
</script>

<!-- Add this CSS at the end of the file to ensure proper modal behavior -->
<style>
/* Override modal backdrop to ensure it doesn't break site layout */
.modal-backdrop {
    position: fixed;
    top: 0;
    left: 0;
    opacity: 0.5 !important;
    z-index: 1040 !important;
    width: 100vw;
    height: 100vh;
    background-color: rgba(0, 0, 0, 0.5);
}
/* Force fix for body padding and scrollbar issues */
body {
    overflow-y: auto !important;
    padding-right: 0 !important;
}
/* Ensure body padding doesn't affect layout when modal is closed */
body.modal-open {
    overflow: hidden;
    padding-right: 0 !important;
}

/* Improve test result containers */
.test-results-output {
    max-height: 70vh;
    overflow-y: auto;
    padding: 15px;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    background-color: #f8f9fa;
}

/* Make sure modal content doesn't overflow */
.modal-body {
    max-height: 75vh;
    overflow-y: auto;
}
/* Ensure modals don't affect page layout */
.modal {
    padding-right: 0 !important;
}

/* Override any bootstrap scroll locking */
html {
    overflow: auto !important;
}

/* Fix for iOS and other mobile devices */
html, body {
    position: relative !important;
    height: auto !important;
}

/* Clean up any fixed positioning that might be lingering */
.modal-open {
    position: static !important;
}

/* Force scrollbar to be consistent */
::-webkit-scrollbar {
    width: initial !important;
}
</style>
