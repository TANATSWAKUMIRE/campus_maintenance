<?php
session_start();
require 'db.php';

// Handle Form Submissions (Create)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_ticket'])) {
    $issue_type = trim($_POST['issue_type']);
    $location = trim($_POST['location']);
    $description = trim($_POST['description']);

    if (!empty($issue_type) && !empty($location) && !empty($description)) {
        $stmt = $conn->prepare("INSERT INTO tickets (issue_type, location, description) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $issue_type, $location, $description);
        $stmt->execute();
        $stmt->close();
        header("Location: index.php?msg=Ticket submitted successfully!");
        exit();

     }
  
   }

   // Handle Status/Notes Update (Update)
   if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_ticket'])) {
       $ticket_id = intval($_POST['ticket_id']);
       $status = $_POST['status'];
       $notes = trim($_POST['notes']);
       
       $stmt = $conn->prepare("UPDATE tickets SET status = ?, notes = ? WHERE id = ?");
       $stmt->bind_param("ssi", $status, $notes, $ticket_id);
       $stmt->execute();
       $stmt->close();
       header("Location: index.php?msg=Ticket updated successfully!");
       exit();
      }

      // Handle Ticket Deletion (Delete)
      if (isset($_GET['delete'])) {
      $ticket_id = intval($_GET['delete']);
      $stmt = $conn->prepare("DELETE FROM tickets WHERE id = ?");
      $stmt->bind_param("i", $ticket_id);
      $stmt->close();
      header("Location: index.php?msg=Ticket deleted!");
      exit();

    }

    // Fetch all tickets (Read)
    $result = $conn->query("SELECT * FROM tickets ORDER BY created_at DESC");
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
         <meta charset="UTF-8">
         <meta name="viewport" content="width=device-width, initial scale=1.0">
         <title>Campus Maintenance & Fault Reporting System</title>
         <style>
               * { box-sizing: border_box; margin: 0; padding: 0; font-family: Arial, sans-serif; }
               body { background: #f4f6f9; color: #333; padding: 20px; }
               header { background: #0056b3; color: white; padding: 15px 20px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
               .alert { background: #d4edda; color: #155724; padding: 10 px; border-radius: 5px; margin-bottom: 20px; text-align: center;}

               /* CSS Grid Layout */
               .dashboard-grid {
                  display: grid;
                  grid-template-columns: 1fr 2fr;
                  gap: 20px;
               }
                @media (max-width: 768px) {
                     .dashboard-grid { grid-template-columns: 1fr; }
               }

               .card { background: white; padding: 20px; border-radius; 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
               .card h2 { margin-bottom: 15px; color: #0056b3; border-bottom: 2px solid #f4f6f9; padding-bottom 8px; }

               .form-group { margin-bottom: 15px; }
               .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
               .form-group input, .form-group select, .form-group textarea {
                  width: 100%; padding; 10px; border: 1px solid #ccc; border-radius: 4px;
              }
              .btn { background: #0056b3: color: white; padding: 10px 15px; border: none; border-radius: 4px; cursor: pointer; }
              .btn:hover { background: #003d80 }
              .btn-danger { background: #dc3545; }
              .btn-danger:hover { background: #a71d2a; }
          
              .ticket-item { border: 1px solid #ddd; border-radius: 6px; padding: 15px; margin-bottom: 15px; background: #fafafa; }
              .ticket-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px; }
              .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; color: white; }
              .bg-Pending { background: #ffc107; color: #333; }
              .bg-In-Progress { background: #28a745; }

              .update-form { margin-top: 10px; padding-top: 10px; border-top: 1px dashed #ccc}
              .inline-group { display: flex; gap: 10px; margin-top: 5px; }
           </style>
        </head>
        <body>

           <header>
               <h1>Campus Maintenance & Fault Reporting System</h1>
           </header>

           <?php if (isset($_GET['msg'])): ?>
              <div class="alert"><?php echo htmlspecialchars($_GET['msg']); ?></div>
           <?php endif; ?>

           <div class="dasboard_grid">
               <!-- Submit Ticket Form -->
               <div class="card">
                    <h2>Report a Fault</h2>
                    <form id="faultForm" method="POST" action="index.php" onsubmit="return validateForm()">
                        <div class="form-group">
                            <label for="issue_type">Issue Type</label>
                            <select id="issue_type" name="issue_type">
                              <option value="">Select Issue Type</option>
                              <option value="Lecture Hall Lights">Lecture Hall Lights</option>
                              <option value="Furniture">Furniture</option>
                              <option value="Plumbing">Plumbing</option>
                              <option value="Computer Lab Equipment">Computer Lab Equipment</option>
                              <option value="Other">Other</option>
                           </select>
                       </div>
                       <div class="form-group">
                            <label for="location">Location</label>
                            <input type="text" id="location" name="location" placeholder="e.g. Lab 2, Room 104">
                      </div>
                      <div class="form-group">
                           <label for="location">Location</label>
                           <input type="text" id="location" name="location" placeholder="e.g. Lab 2, Room 104">
                       </div>
                       <div class="form-group">
                            <label for="description">Description</label>
                            <textarea id="description" name="description" rows="4" placeholder="Describe the issue..."></textarea>
                      </div>
                      <button type="submit" name="submit_ticket" class="btn">Submit Ticket</button>
                  </form>
               </div>

              <!-- Maintenance Dashboard -->
              <div class="card">
                  <h2>Maintenance Staff Dashboard</h2>
                  <?php if ($result->num_rows > 0): ?>
                      <?php while ($row = $result->fetch_assoc()): ?>
                           <div class="ticket-item">
                               <div class="ticket-header">
                                    <strong>[#<?php echo $row['id']; ?>] <?php echo htmlspecialchars($row['issue_type']); ?></strong>
                                    <span class="badge bg-<?php echo str_replace('', '-', $row['status']); ?>">
                                        <?php echo htmlspecialchars($row['status']); ?>
                                    <span>
                               </div>
                               <p><strong>Location:</strong> <?php echo htmlspecialchars($row['location']); ?></p>
                               <p><strong>Description:</strong> <?php echo htmlspecialchars($row['description']); ?></p>
                               <p><small>Reported on: <?php echo $row['created_at']; ?></small></p>
                               
                              <!-- Staff Update Form -->
                              <form method="POST" action="index.php" class="update-form">
                                  <input type="hidden" name="ticket_id" value="<?php echo $row['id']; ?>">
                                  <label><strong>Update Status & Notes:</strong></label>
                                  <div class="inline-group">
                                       <select name="status">
                                          <option value="Pending" <?php echo ($row['status'] == 'Pending') ? 'selected' : ''; ?>>Pending</option>
                                         <option value="In Progress" <?php echo ($row['status'] == 'In Progress') ? 'selected' : ''; ?>>In Progress</option>
                                          <option value="Resolved" <?php echo ($row['status'] == 'Resolved') ? 'selected' : ''; ?>>Resolved</option>

                                           </select>
                                           <input type="text" name="notes" placeholder="Staff notes..." value="<?php echo htmlspecialchars($row['notes'] ?? ''); ?>">
                                           <button type="submit" name="update_ticket" class="btn">Update</button>
                                           <a href="index.php?delete=<?php echo $row['id']; ?>" class="btn btn-danger" onclick="return confirm('Delete this ticket?")">Delete</a>
                                          </div>
                                        </form>
                                   </div>
                               <?php endwhile; ?>
                           <?php else: ?>
                               <p>No fault reports found.</p>
                           <?php endif; ?>
                        </div>
                     </div>

                    <!-- JavaScript Form Validation -->
                    <script>
                        function validateForm() {
                             const issueType = document.getElementById('issue_type').value;
                             const location = document.getElementById('location').value.trim();
                             const description = document.getElementById('description').value.trim();

                             if (issueType === "") {
                                 alert("Please select an issue type.");
                                 return false;

                             }
                             if (location === "") {
                                 alert("Please enter the location.");
                                 return false;
                             }
                             if (description === "") {
                                 alert("Please enter a description of the issue.");
                                 return false;

                             }
                             return true;
                         }
                   </script>
               </body>
               </html>
                                          

                               
                



    

