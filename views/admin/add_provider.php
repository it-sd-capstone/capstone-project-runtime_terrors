<?php include VIEW_PATH . '/partials/header.php'; ?>

<div class="container my-4">
    <div class="row">
        <div class="col-md-8 offset-md-2">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4>Add New Provider</h4>
                </div>
                <div class="card-body">
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= $error ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" role="alert">
                            <?= $success ?>
                        </div>
                    <?php endif; ?>
                    
                    <form method="POST" action="<?= base_url('index.php/admin/addProvider') ?>">
                        <?= csrf_field() ?>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="first_name" class="form-label">First Name *</label>
                                <input type="text" class="form-control" id="first_name" name="first_name" required>
                            </div>
                            <div class="col-md-6">
                                <label for="last_name" class="form-label">Last Name *</label>
                                <input type="text" class="form-control" id="last_name" name="last_name" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">Email Address *</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">Phone Number</label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="(123) 456-7890">
                            <div class="form-text">Enter a 10-digit phone number</div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <div class="form-text text-info">
                                <i class="fas fa-info-circle"></i> A secure temporary password will be automatically generated when the account is created. You'll see it once after submission.
                            </div>
                        </div>
                        
                        <!-- Provider-specific fields -->
                        <hr>
                        <!-- <h5 class="mb-3">Provider Details</h5>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="title" class="form-label">Title</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="MD, NP, PA, etc.">
                            </div>
                            <div class="col-md-6">
                                <label for="specialization" class="form-label">Specialization</label>
                                <input type="text" class="form-control" id="specialization" name="specialization">
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="bio" class="form-label">Biography</label>
                            <textarea class="form-control" id="bio" name="bio" rows="3"></textarea>
                        </div>
                        
                        <div class="mb-3">
                            <label for="max_patients_per_day" class="form-label">Maximum Patients Per Day</label>
                            <input type="number" class="form-control" id="max_patients_per_day" name="max_patients_per_day" min="1" value="10">
                        </div> -->
                        
                        <?php if (!empty($services)): ?>
                        <div class="mb-3">
                            <label class="form-label">Services Offered</label>
                            <div class="row">
                                <?php foreach ($services as $service): ?>
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="services[]" value="<?= $service['service_id'] ?>" id="service_<?= $service['service_id'] ?>">
                                        <label class="form-check-label" for="service_<?= $service['service_id'] ?>">
                                            <?= htmlspecialchars($service['name']) ?>
                                        </label>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <?php endif; ?>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                            <a href="<?= base_url('index.php/admin/users') ?>" class="btn btn-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">Add Provider</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Phone number formatting
    const phoneInput = document.getElementById('phone');
    
    if (phoneInput) {
        phoneInput.addEventListener('input', function(e) {
            // Get input value and remove non-numeric characters
            let input = e.target.value.replace(/\D/g, '');
            
            // Limit to 10 digits
            input = input.substring(0, 10);
            
            // Format the number as (XXX) XXX-XXXX
            if (input.length > 0) {
                if (input.length <= 3) {
                    input = '(' + input;
                } else if (input.length <= 6) {
                    input = '(' + input.substring(0, 3) + ') ' + input.substring(3);
                } else {
                    input = '(' + input.substring(0, 3) + ') ' + input.substring(3, 6) + '-' + input.substring(6);
                }
            }
            
            // Update the input value
            e.target.value = input;
        });
        
        // Handle special cases like backspace and delete
        phoneInput.addEventListener('keydown', function(e) {
            // Allow backspace, delete, tab, escape, enter and navigation keys
            if (e.key === 'Backspace' || e.key === 'Delete') {
                let value = e.target.value;
                let caretPos = e.target.selectionStart;
                
                // If cursor is after a formatting character, move it past the character
                if (
                    (caretPos === 1 && value.charAt(0) === '(') ||
                    (caretPos === 6 && value.charAt(5) === ')') ||
                    (caretPos === 11 && value.charAt(10) === '-')
                ) {
                    e.preventDefault();
                    e.target.setSelectionRange(caretPos - 1, caretPos - 1);
                }
            }
        });
    }
});
</script>

<?php include VIEW_PATH . '/partials/footer.php'; ?>