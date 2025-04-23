<h4>Appointment Requests</h4>
<table class="table">
    <thead>
        <tr>
            <th>Patient</th>
            <th>Date</th>
            <th>Time</th>
            <th>Action</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($requests as $request) : ?>
            <tr>
                <td><?= htmlspecialchars($request['patient_name']) ?></td>
                <td><?= htmlspecialchars($request['date']) ?></td>
                <td><?= htmlspecialchars($request['time']) ?></td>
                <td>
                    <a href="<?= base_url('index.php/provider/approveRequest/' . $request['id']) ?>" class="btn btn-success">Approve</a>
                    <a href="<?= base_url('index.php/provider/declineRequest/' . $request['id']) ?>" class="btn btn-danger">Decline</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>