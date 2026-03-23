<!DOCTYPE html>
<html lang="vi">
<head>
 <meta charset="UTF-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
   <title> ChickenJoy Admin | Quản Lý Đơn Đặt Hàng</title>

   <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
 <link rel="stylesheet" href="../assets/css/admin.css" />
</head>

<body class="admin-body">

   <?php include 'layout/sidebar.php'; ?>

   <main class="main-content">
  
      <header class="main-header">
   <h1>Quản Lý Đơn Đặt Hàng</h1>
   </header>

      
   
         <div class="filters">
    <div class="filter">
     <label>Từ ngày:</label>
     <input type="date" id="fromDate" />
    </div>
    <div class="filter">
     <label>Đến ngày:</label>
     <input type="date" id="toDate" />
    </div>
    <div class="filter">
     <label>Tình trạng:</label>
     <select id="statusFilter">
      <option value="all">Tất cả</option>
      <option value="new">Mới đặt</option>
      <option value="processing">Đã xử lý</option>
      <option value="delivered">Đã giao</option>
      <option value="cancelled">Hủy</option>
     </select>
    </div>
   </div>

   <section class="table-section">
         <table class="data-table">
    <thead>
     <tr>
      <th>Mã đơn</th>
      <th>Khách hàng</th>
      <th>Ngày đặt</th>
      <th>Tổng tiền</th>
      <th>Tình trạng</th>
      <th>Thao tác</th>
     </tr>
    </thead>
    <tbody>
     <tr>
      <td>DH001</td>
      <td>Nguyễn Văn A</td>
      <td>2025-10-25</td>
      <td>185.000đ</td>
                  <td><span class="status new"><img src="../assets/images/icons/clock-three.png" alt="mới đặt" class="icon"> Mới đặt</span></td>
      <td>
              <div class="actions">
       <a href="order-detail.html" class="btn-view">
        <img src="../assets/images/icons/eye.png" alt="Xem chi tiết" class="icon-eye">
        Xem chi tiết
       </a>
              </div>
      </td>
    </tr>
    <tr>
     <td>DH002</td>
     <td>Trần Thị B</td>
     <td>2025-10-27</td>
     <td>215.000đ</td>
      <td><span class="status delivered"><img src="../assets/images/icons/open-box-check.png" alt="đã giao" class="icon"> Đã giao</span></td>
      <td>
       <div class="actions">
              <a href="order-detail.html" class="btn-view">
        <img src="../assets/images/icons/eye.png" alt="Xem chi tiết" class="icon-eye">
        Xem chi tiết
       </a>
            </div>
      </td>
    </tr>
    <tr>
     <td>DH003</td>
     <td>Lê Văn C</td>
     <td>2025-10-28</td>
     <td>152.000đ</td>
      <td><span class="status cancelled"><img src="../assets/images/icons/cross-small.png" alt="hủy" class="icon"> Hủy</span></td>
      <td>
       <div class="actions">
              <a href="order-detail.html" class="btn-view">
        <img src="../assets/images/icons/eye.png" alt="Xem chi tiết" class="icon-eye">
        Xem chi tiết
       </a>
            </div>
      </td>
    </tr>
    <tr>
     <td>DH004</td>
     <td>Phạm Thu D</td>
     <td>2025-10-29</td>
     <td>198.000đ</td>
      <td><span class="status processing"><img src="../assets/images/icons/check.png" alt="xử lý" class="icon"> Đã xử lý</span></td>
      <td>
       <div class="actions">
              <a href="order-detail.html" class="btn-view">
        <img src="../assets/images/icons/eye.png" alt="Xem chi tiết" class="icon-eye">
        Xem chi tiết
       </a>
            </div>
      </td>
    </tr>
   </tbody>
   </table>

  </section>
 </main>
</body>
</html>