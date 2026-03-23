<!DOCTYPE html>
<html lang="vi">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="icon" type="image/png" href="../assets/images/logo-1.png" />
  <title> ChickenJoy Admin | Quản Lý Người Dùng</title>
  <link rel="stylesheet" href="../assets/css/admin.css" />
</head>

<body class="admin-body">

   <?php include 'layout/sidebar.php'; ?>
  <main class="main-content">
    
    <header class="main-header">
      <h1>Quản Lý Người Dùng</h1>
      
      <div class="header-actions">
        <a href="#" class="btn-primary">+ Thêm người dùng</a>
      </div> </header>

    
    <div class="table-toolbar">
      <input type="text" placeholder="🔍 Tìm kiếm người dùng..." />
    </div>
    
    <section class="table-section">
      <table class="data-table">
        <thead>
          <tr>
            <th>ID</th>
            <th>Họ tên</th>
            <th>Email</th>
            <th>Số điện thoại</th>
            <th>Mật khẩu</th>
            <th>Trạng thái</th>
            <th>Thao tác</th>
          </tr>
        </thead>

        <tbody>
          <tr>
            <td>U001</td>
            <td>Nguyễn Văn A</td>
            <td>vana@gmail.com</td>
            <td>0903 456 789</td>
            <td>abc123</td>
            <td><span class="status active">Hoạt động</span></td>
            <td>
              <div class="actions">
                <a href="#" class="btn-hide">Khóa</a>
                <a href="#" class="btn-edit">Reset Mật khẩu</a>
              </div>
            </td>
          </tr>

          <tr>
            <td>U002</td>
            <td>Trần Thị B</td>
            <td>thib@gmail.com</td>
            <td>0778 123 456</td>
            <td>bcd234</td>
            <td><span class="status hidden">Bị khóa</span></td>
            <td>
              <div class="actions">
                <a href="#" class="btn-show">Mở khóa</a>
                <a href="#" class="btn-edit">Reset Mật khẩu</a>
              </div>
            </td>
          </tr>

          <tr>
            <td>U003</td>
            <td>Lê Minh C</td>
            <td>minhc@gmail.com</td>
            <td>0369 987 654</td>
            <td>xyz789</td>
            <td><span class="status active">Hoạt động</span></td>
            <td>
              <div class="actions">
                <a href="#" class="btn-hide">Khóa</a>
                <a href="#" class="btn-edit">Reset Mật khẩu</a>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </section>
  </main>
</body>
</html>