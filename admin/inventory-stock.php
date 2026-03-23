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
    <h1>Tra Cứu Tồn Kho Sản Phẩm</h1>
    </header>

      <a href="inventory.html" class="back-btn">
      <img src="../assets/images/icons/arrow-small-left.png" alt="quay lại"> Quay lại
    </a>
    
      <div class="inventory-container">
      <div class="search-section">
    <h2>Tra cứu tồn kho</h2>
        
        <div class="form-group">
      <label for="product-type">Loại sản phẩm:</label>
      <select id="product-type">
       <option value="">-- Chọn loại --</option>
       <option value="thit-ca">Thịt / Cá</option>
                   <option value="do-dung-dung">Đồ đựng</option>
      </select>
        </div>

        <div class="form-group">
      <label for="product-name">Tên sản phẩm:</label>
      <select id="product-name">
       <option value="">-- Chọn sản phẩm --</option>
       <option>Ức gà phi lê</option>
                   <option>Ống hút</option>
      </select>
        </div>

        <div class="form-group">
      <label for="date">Tại thời điểm:</label>
      <input type="date" id="date" />
        </div>

            <button class="btn-primary">Tra cứu tồn kho</button>

       </div>

      <div class="result-section">
    <h2>Kết quả tra cứu</h2>
            <div class="result-box">
               <div class="result-row">
      <span class="label">Mã SP:</span>
      <span class="value">PD007</span>
     </div>
     <div class="result-row">
      <span class="label">Tên sản phẩm:</span>
      <span class="value">Nước suối</span>
     </div>
     <div class="result-row">
      <span class="label">Loại sản phẩm:</span>
      <span class="value">Nước đóng chai</span>
     </div>

     <div class="result-row">
      <span class="label">Tồn kho:</span>
      <span class="value">35 cái</span>
     </div>
     <div class="result-row">
      <span class="label">Trạng thái:</span>
                  <span class="value status active">Còn hàng</span>
     </div>
              </div>
   </div>
  </div>
     </main>
</body>
</html>