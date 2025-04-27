<h4>Appointment Requests</h4>
<?php if (!empty($requests)) : ?>
    <ul>
        <?php foreach ($requests as $request) : ?>
            <li>
                <?= htmlspecialchars($request['patient_name']) ?> requested <?= htmlspecialchars($request['service_name']) ?> on <?= htmlspecialchars($request['appointment_date']) ?>
                <br>
                <a href="<?= base_url('index.php/provider/approveRequest/' . $request['id']) ?>" class="btn btn-success">Approve</a>
                <a href="<?= base_url('index.php/provider/declineRequest/' . $request['id']) ?>" class="btn btn-danger">Decline</a>
            </li>
        <?php endforeach; ?>
    </ul>
<?php else : ?>
    <p>No pending requests.</p>
<?php endif; ?>