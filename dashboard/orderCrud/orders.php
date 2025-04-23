<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Order Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    <link rel="stylesheet" href="../styles/orders.css" />
  </head>

  <body class="bg-light">
    <div class="container py-4">
      <h1 class="text-center mb-4">Order Management</h1>
      
      <div class="card">
        <div class="card-body">
          <div class="table-responsive">
            <table class="table table-hover">
              <thead class="table-dark">
                <tr>
                  <th>Order ID</th>
                  <th>User</th>
                  <th>Product</th>
                  <th>Status</th>
                  <th>Date</th>
                  <th>Actions</th>
                </tr>
              </thead>
              <tbody id="ordersTable">
                <!-- Orders will be populated here -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
      function loadOrders() {
        let orders = JSON.parse(localStorage.getItem("orders")) || [];
        let tbody = document.getElementById("ordersTable");
        tbody.innerHTML = "";

        if (orders.length === 0) {
          tbody.innerHTML = `
            <tr>
              <td colspan="6" class="text-center py-4">No orders found</td>
            </tr>
          `;
          return;
        }

        orders.forEach((order, index) => {
          let row = document.createElement("tr");
          row.innerHTML = `
            <td>${order.id || index + 1}</td>
            <td>${order.username}</td>
            <td>${order.product.Title}</td>
            <td>
              <span class="badge ${getStatusBadgeClass(order.status)}">
                ${order.status}
              </span>
            </td>
            <td>${new Date(order.date || Date.now()).toLocaleDateString()}</td>
            <td>
              <button class="btn btn-sm btn-success me-2" onclick="updateOrderStatus(${index}, 'completed')">
                <i class="fas fa-check"></i> Complete
              </button>
              <button class="btn btn-sm btn-danger" onclick="updateOrderStatus(${index}, 'cancelled')">
                <i class="fas fa-times"></i> Cancel
              </button>
            </td>
          `;
          tbody.appendChild(row);
        });
      }

      function getStatusBadgeClass(status) {
        switch(status.toLowerCase()) {
          case 'pending': return 'bg-warning text-dark';
          case 'completed': return 'bg-success';
          case 'cancelled': return 'bg-danger';
          default: return 'bg-secondary';
        }
      }

      function updateOrderStatus(index, newStatus) {
        let orders = JSON.parse(localStorage.getItem("orders")) || [];
        if (index >= 0 && index < orders.length) {
          orders[index].status = newStatus;
          localStorage.setItem("orders", JSON.stringify(orders));
          loadOrders();
          
          // Show notification
          const toast = new bootstrap.Toast(document.getElementById('notificationToast'));
          document.getElementById('notification-message').textContent = `Order status updated to ${newStatus}`;
          toast.show();
        }
      }

      document.addEventListener("DOMContentLoaded", function () {
        loadOrders();
      });
    </script>
  </body>
</html>