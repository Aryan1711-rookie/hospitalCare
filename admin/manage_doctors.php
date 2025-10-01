<?php
session_start();
$page_title = "Manage Doctors";

// --- NEW: INCLUDE DATABASE CONNECTION AT THE TOP ---
require_once '../includes/db.php';

// --- NEW: PHP LOGIC TO HANDLE THE EDIT FORM SUBMISSION ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    // Get the submitted data
    $doctorId = $_POST['doctor_id'];
    $fullName = $_POST['full_name'];
    $specialization = $_POST['specialization'];
    $contactNumber = $_POST['contact_number'];

    // Validate the data
    if (!empty($doctorId) && !empty($fullName) && !empty($specialization) && !empty($contactNumber)) {
        // Prepare the UPDATE statement
        $sqlUpdate = "UPDATE doctors SET full_name = ?, specialization = ?, contact_number = ? WHERE doctor_id = ?";
        $stmt = $conn->prepare($sqlUpdate);
        // Bind parameters: s = string, i = integer
        $stmt->bind_param("sssi", $fullName, $specialization, $contactNumber, $doctorId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Doctor details updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update doctor details. Error: " . $stmt->error;
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "All fields are required for an update.";
    }

    // Redirect back to the same page to show the result and prevent form resubmission
    header("Location: manage_doctors.php");
    exit();
}
// --- END OF EDIT LOGIC ---


// Admin access guard
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header("Location: ../auth/login.php");
    exit();
}

// Fetch all doctors from the database, ordering by the newest first
$sql = "SELECT doctor_id, full_name, specialization, contact_number FROM doctors ORDER BY doctor_id DESC";
$result = $conn->query($sql);

include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">Manage Doctors</h2>
        <a href="add_doctor.php" class="btn btn-success"><i class="bi bi-plus-circle"></i> Add Doctor</a>
    </div>

    <?php if (isset($_SESSION['success_message'])): ?>
        <div class="alert alert-success"><?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?></div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error_message'])): ?>
        <div class="alert alert-danger"><?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?></div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-body">
            <table class="table table-hover align-middle">
                <thead class="table-primary">
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Specialization</th>
                        <th>Phone</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($doctor = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($doctor['doctor_id']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['specialization']); ?></td>
                                <td><?php echo htmlspecialchars($doctor['contact_number']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editDoctorModal"
                                        data-id="<?php echo htmlspecialchars($doctor['doctor_id']); ?>"
                                        data-name="<?php echo htmlspecialchars($doctor['full_name']); ?>"
                                        data-specialization="<?php echo htmlspecialchars($doctor['specialization']); ?>"
                                        data-phone="<?php echo htmlspecialchars($doctor['contact_number']); ?>">
                                        Edit
                                    </button>

                                    <button class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deleteDoctorModal"
                                        data-id="<?php echo htmlspecialchars($doctor['doctor_id']); ?>"
                                        data-name="<?php echo htmlspecialchars($doctor['full_name']); ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">No doctors found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editDoctorModal" tabindex="-1" aria-labelledby="editDoctorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="manage_doctors.php" method="POST" class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="editDoctorModalLabel">Edit Doctor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="doctor_id" id="edit-id">

        <div class="mb-3">
            <label for="edit-name" class="form-label">Doctor Name</label>
            <input type="text" class="form-control" id="edit-name" name="full_name" required>
        </div>
        <div class="mb-3">
            <label for="edit-specialization" class="form-label">Specialization</label>
            <input type="text" class="form-control" id="edit-specialization" name="specialization" required>
        </div>
        <div class="mb-3">
            <label for="edit-phone" class="form-label">Phone</label>
            <input type="text" class="form-control" id="edit-phone" name="contact_number" required>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="deleteDoctorModal" tabindex="-1" aria-labelledby="deleteDoctorModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="delete_doctor.php" method="POST" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deleteDoctorModalLabel">Delete Doctor</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="doctor_id" id="delete-id">
        <p>Are you sure you want to delete <strong id="delete-name"></strong>?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-danger">Yes, Delete</button>
      </div>
    </form>
  </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Edit modal event listener
    const editModal = document.getElementById("editDoctorModal");
    editModal.addEventListener("show.bs.modal", event => {
        const button = event.relatedTarget;
        // Get data from data-* attributes
        const id = button.getAttribute("data-id");
        const name = button.getAttribute("data-name");
        const specialization = button.getAttribute("data-specialization");
        const phone = button.getAttribute("data-phone");
        
        // Populate the modal's form fields
        document.getElementById("edit-id").value = id;
        document.getElementById("edit-name").value = name;
        document.getElementById("edit-specialization").value = specialization;
        document.getElementById("edit-phone").value = phone;
    });

    // Delete modal event listener
    const deleteModal = document.getElementById("deleteDoctorModal");
    deleteModal.addEventListener("show.bs.modal", event => {
        const button = event.relatedTarget;
        document.getElementById("delete-id").value = button.getAttribute("data-id");
        document.getElementById("delete-name").textContent = button.getAttribute("data-name");
    });
});
</script>

<?php 
$conn->close();
include '../includes/footer.php'; 
?>