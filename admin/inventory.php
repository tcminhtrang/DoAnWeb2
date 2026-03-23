<!DOCTYPE html>
<html lang="vi">
<head>
 <meta charset="UTF-8" />
 <meta name="viewport" content="width=device-width, initial-scale=1.0" />
 <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
 <title> ChickenJoy Admin | Quản Lý Tồn Kho</title>
   <link rel="stylesheet" href="../assets/css/admin.css" />
</head>
<body class="admin-body">

   <?php include 'layout/sidebar.php'; ?>

   <main class="main-content">
      <header class="main-header">
   <h1>Quản Lý Tồn Kho</h1>
        </header>

      <div class="low-stock-alert">
    <h3> 
      <img src="../assets/images/icons/triangle-warning.png" alt="cảnh báo" class="alert-icon">
      Cảnh báo: Sản phẩm sắp hết hàng (tồn &lt; 10)
    </h3>
    <ul>
      <li>Phô mai lát: còn 2 lát</li>
      <li>Ống hút: còn 3 cái</li>
      <li>Hộp đựng gà rán: còn 4 cái</li>
    </ul>
  </div>

      
         <div class="table-toolbar">
    <input type="text" placeholder="🔍 Tìm kiếm sản phẩm..." />
            <a href="inventory-stock.html" class="btn-primary">Tra cứu tồn kho</a>
    <a href="inventory-movement.html" class="btn-primary">Tra cứu N-X-T</a>
   </div>
   <section class="table-section">
      <table class="data-table">
    <thead>
     <tr>
      <th>Mã SP</th>
      <th>Tên sản phẩm</th>
      <th>Loại</th>
      <th>Tồn đầu kỳ</th>
      <th>Nhập</th>
      <th>Xuất</th>
      <th>Tồn cuối kỳ</th>
      <th>Trạng thái</th>
     </tr>
    </thead>
    <tbody>
     <tr>
      <td>PD001</td>
      <td>Thịt bò xay</td>
      <td>Thịt/Cá</td>
      <td>10</td>
      <td>8</td>
      <td>5</td>
      <td>13</td>
                  <td><span class="status active">Còn hàng</span></td>
     </tr>
     <tr>       <td>PD002</td>
      <td>Phô mai lát</td>
      <td>Sốt/Phụ gia</td>
      <td>5</td>
      <td>7</td>
      <td>3</td>
      <td>2</td>
                  <td><span class="status cancelled">Sắp hết hàng</span></td>
     </tr>
     <tr>
      <td>PD003</td>
      <td>Khoai tây đông lạnh</td>
      <td>Rau củ</td>
      <td>15</td>
      <td>10</td>
      <td>5</td>
      <td>20</td>
      <td><span class="status active">Còn hàng</span></td>
     </tr>
     <tr>
      <td>PD004</td>
      <td>Ống hút</td>
      <td>Đồ dùng</td>
      <td>8</td>
      <td>8</td>
      <td>5</td>
      <td>3</td>
      <td><span class="status cancelled">Sắp hết hàng</span></td>
     </tr>
     <tr>
      <td>PD005</td>
      <td>Sốt cà chua</td>
      <td>Sốt/Phụ gia</td>
      <td>12</td>
      <td>10</td>
      <td>8</td>
      <td>14</td>
      <td><span class="status active">Còn hàng</span></td>
     </tr>
     <tr>
      <td>PD006</td>
      <td>Bánh burger</td>
      <td>Đồ khô/Gia vị</td>
      <td>25</td>
      <td>5</td>
      <td>3</td>
      <td>27</td>
      <td><span class="status active">Còn hàng</span></td>
     </tr>
     <tr>
      <td>PD007</td>
      <td>Nước suối</td>
      <td>Nước đóng chai</td>
      <td>30</td>
      <td>10</td>
      <td>5</td>
      <td>35</td>
      <td><span class="status active">Còn hàng</span></td>
     </tr>
     <tr>
      <td>PD008</td>
      <td>Trà đóng chai</td>
      <td>Nước đóng chai</td>
      <td>20</td>
      <td>5</td>
      <td>3</td>
      <td>22</td>
      <td><span class="status active">Còn hàng</span></td>
     </tr>
     <tr>
      <td>PD009</td>
      <td>Hộp đựng gà rán</td>
      <td>Đồ đựng</td>
      <td>6</td>
      <td>9</td>
      <td>2</td>
      <td>4</td>
      <td><span class="status cancelled">Sắp hết hàng</span></td>
     </tr>
     <tr>
      <td>PD010</td>
      <td>Bột trà sữa</td>
      <td>Nguyên liệu pha chế khác</td>
      <td>10</td>
      <td>10</td>
      <td>8</td>
      <td>12</td>
      <td><span class="status active">Còn hàng</span></td>
     </tr>
    </tbody>
   </table>
   </section>
 </main>
</body>
</html>