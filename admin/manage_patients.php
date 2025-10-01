<?php
session_start();
$page_title = "Manage Patients";

// --- Database Connection ---
require_once '../includes/db.php';

// --- Handle Edit Patient Form Submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_patient') {
    $patientId = $_POST['patient_id'];
    $dateOfBirth = $_POST['date_of_birth'];
    $gender = $_POST['gender'];
    $contactNumber = $_POST['contact_number'];
    $address = $_POST['address'];

    if (!empty($patientId) && !empty($dateOfBirth) && !empty($gender) && !empty($contactNumber) && !empty($address)) {
        $sqlUpdate = "UPDATE patients SET date_of_birth = ?, gender = ?, contact_number = ?, address = ? WHERE patient_id = ?";
        $stmt = $conn->prepare($sqlUpdate);
        $stmt->bind_param("ssssi", $dateOfBirth, $gender, $contactNumber, $address, $patientId);

        if ($stmt->execute()) {
            $_SESSION['success_message'] = "Patient details updated successfully!";
        } else {
            $_SESSION['error_message'] = "Failed to update patient details.";
        }
        $stmt->close();
    } else {
        $_SESSION['error_message'] = "All fields are required.";
    }
    header("Location: manage_patients.php");
    exit();
}


// --- Admin Access Guard ---
if (!isset($_SESSION['loggedin']) || $_SESSION['role'] !== 'admin') {
    $_SESSION['error_message'] = "You do not have permission to access this page.";
    header("Location: ../auth/login.php");
    exit();
}


// --- Fetch All Patients from Database ---
$sql = "SELECT p.patient_id, u.name, p.date_of_birth, p.gender, p.contact_number, p.address
        FROM patients p
        JOIN users u ON p.user_id = u.user_id
        ORDER BY p.patient_id DESC";
$result = $conn->query($sql);


include '../includes/header.php';
?>

<div class="container my-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-primary">Manage Patients</h2>
        <a href="../patients/add.php" class="btn btn-info text-white">View Add Patient Form</a>
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
                        <th>Date of Birth</th>
                        <th>Gender</th>
                        <th>Contact</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($patient = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($patient['patient_id']); ?></td>
                                <td><?php echo htmlspecialchars($patient['name']); ?></td>
                                <td><?php echo htmlspecialchars($patient['date_of_birth']); ?></td>
                                <td><?php echo htmlspecialchars($patient['gender']); ?></td>
                                <td><?php echo htmlspecialchars($patient['contact_number']); ?></td>
                                <td>
                                    <button class="btn btn-sm btn-warning"
                                        data-bs-toggle="modal"
                                        data-bs-target="#editPatientModal"
                                        data-id="<?php echo htmlspecialchars($patient['patient_id']); ?>"
                                        data-name="<?php echo htmlspecialchars($patient['name']); ?>"
                                        data-dob="<?php echo htmlspecialchars($patient['date_of_birth']); ?>"
                                        data-gender="<?php echo htmlspecialchars($patient['gender']); ?>"
                                        data-contact="<?php echo htmlspecialchars($patient['contact_number']); ?>"
                                        data-address="<?php echo htmlspecialchars($patient['address']); ?>">
                                        Edit
                                    </button>
                                    <button class="btn btn-sm btn-danger"
                                        data-bs-toggle="modal"
                                        data-bs-target="#deletePatientModal"
                                        data-id="<?php echo htmlspecialchars($patient['patient_id']); ?>"
                                        data-name="<?php echo htmlspecialchars($patient['name']); ?>">
                                        Delete
                                    </button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">No patient profiles found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="editPatientModal" tabindex="-1" aria-labelledby="editPatientModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="manage_patients.php" method="POST" class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="editPatientModalLabel">Edit Patient Details</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="action" value="update_patient">
        <input type="hidden" name="patient_id" id="edit-id">

        <div class="mb-3">
            <label class="form-label">Patient Name</label>
            <input type="text" class="form-control" id="edit-name" readonly disabled>
        </div>
        <div class="mb-3">
            <label for="edit-dob" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="edit-dob" name="date_of_birth" required>
        </div>
        <div class="mb-3">
            <label for="edit-gender" class="form-label">Gender</label>
            <select class="form-select" id="edit-gender" name="gender" required>
                <option value="Male">Male</option>
                <option value="Female">Female</option>
                <option value="Other">Other</option>
            </select>
        </div>
        <div class="mb-3">
            <label for="edit-contact" class="form-label">Contact Number</label>
            <input type="text" class="form-control" id="edit-contact" name="contact_number" required>
        </div>
        <div class="mb-3">
            <label for="edit-address" class="form-label">Address</label>
            <textarea class="form-control" id="edit-address" name="address" rows="3" required></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<div class="modal fade" id="deletePatientModal" tabindex="-1" aria-labelledby="deletePatientModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form action="delete_patient.php" method="POST" class="modal-content">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title" id="deletePatientModalLabel">Delete Patient</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="patient_id" id="delete-id">
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
    // Edit Patient
    const editModal = document.getElementById("editPatientModal");
    editModal.addEventListener("show.bs.modal", event => {
        const button = event.relatedTarget;
        document.getElementById("edit-id").value = button.getAttribute("data-id");
        document.getElementById("edit-name").value = button.getAttribute("data-name");
        document.getElementById("edit-dob").value = button.getAttribute("data-dob");
        document.getElementById("edit-gender").value = button.getAttribute("data-gender");
        document.getElementById("edit-contact").value = button.getAttribute("data-contact");
        document.getElementById("edit-address").value = button.getAttribute("data-address");
    });

    // Delete Patient
    const deleteModal = document.getElementById("deletePatientModal");
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