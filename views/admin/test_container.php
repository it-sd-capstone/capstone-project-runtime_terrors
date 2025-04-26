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
                            <div class="col-md-6">
                                <h6>Available Tests</h6>
                                <div class="list-group mb-3">
                                    <?php foreach ($testFiles as $testName => $fileName): ?>
                                        <button type="button" class="list-group-item list-group-item-action test-item" 
                                                data-test="<?= htmlspecialchars($testName) ?>">
                                            <?= htmlspecialchars(ucwords(str_replace('_', ' ', $testName))) ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6>Test Information</h6>
                                <div class="alert alert-info">
                                    <p>Select a test from the list to run it. Test results will be displayed in a modal window.</p>
                                    <p><strong>Note:</strong> Some tests may take a few moments to complete.</p>
                                </div>
                                <button id="runSelectedTest" class="btn btn-primary" disabled>
                                    Run Selected Test
                                </button>
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
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    let selectedTest = null;
    const testItems = document.querySelectorAll('.test-item');
    const runButton = document.getElementById('runSelectedTest');
    
    // Handle test selection
    testItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove active class from all items
            testItems.forEach(i => i.classList.remove('active'));
            
            // Add active class to selected item
            this.classList.add('active');
            
            // Store selected test
            selectedTest = this.dataset.test;
            
            // Enable run button
            runButton.disabled = false;
        });
    });
    
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
        fetch('<?= base_url('index.php/admin/runTest') ?>?test=' + encodeURIComponent(selectedTest))
            .then(response => response.text())
            .then(html => {
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
    
    // Handle download results button
    document.getElementById('downloadTestResults').addEventListener('click', function() {
        const resultsHtml = document.getElementById('testResultsContainer').innerHTML;
        const testName = selectedTest || 'test_results';
        
        // Create a blob with the HTML content
        const blob = new Blob([
            '<!DOCTYPE html><html><head><title>Test Results</title>' +
            '<style>' +
            'body { font-family: Arial, sans-serif; line-height: 1.6; padding: 20px; }' +
            'h1 { color: #333; border-bottom: 2px solid #eee; padding-bottom: 10px; }' +
            'h2 { color: #444; margin-top: 30px; border-bottom: 1px solid #eee; padding-bottom: 5px; }' +
            '.alert-success { background-color: #d4edda; color: #155724; border: 1px solid #c3e6cb; padding: 10px; margin-bottom: 10px; border-radius: 4px; }' +
            '.alert-danger { background-color: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; padding: 10px; margin-bottom: 10px; border-radius: 4px; }' +
            '</style></head><body>' +
            '<h1>Test Results: ' + testName.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) + '</h1>' +
            resultsHtml +
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