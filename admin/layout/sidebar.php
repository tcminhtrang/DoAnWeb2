<?php
  // $current_page lúc này chỉ mang giá trị ví dụ như 'category.php', 'list.php'...
  $current_page = basename($_SERVER['PHP_SELF']);
?>
<aside class="sidebar">
    <div class="sidebar-header">
      <img src="../assets/images/logo.png" alt="Logo" class="logo-img">
      <h2><span> ChickenJoy</span></h2>
    </div>
    <nav class="sidebar-menu">
      <a href="../admin/dashboard.php" class="<?php if($current_page == 'dashboard.php') echo 'active'; ?>">
        <img src="../assets/images/icons/home.png" alt="Trang chủ">
        <span>Trang chủ</span>
      </a>
      <a href="../admin/user-management.php" class="<?php if($current_page == 'user-management.php') echo 'active'; ?>">
        <img src="../assets/images/icons/user-add.png" alt="Quản lý người dùng">
        <span>Quản lý người dùng</span>
      </a>
      <a href="../admin/category.php" class="<?php if($current_page == 'category.php' || $current_page == 'category-add.php' || $current_page == 'category-edit.php') echo 'active'; ?>">    
        <img src="../assets/images/icons/burger.png" alt="Loại sản phẩm">
        <span>Loại sản phẩm</span>
      </a>
      <a href="../admin/list.php" class="<?php if($current_page == 'list.php' || $current_page == 'list-add.php' || $current_page == 'list-edit.php') echo 'active'; ?>">
        <img src="../assets/images/icons/app.png" alt="Danh mục sản phẩm">
        <span>Danh mục sản phẩm</span>
      </a>
      <a href="../admin/import.php" class="<?php if($current_page == 'import.php' || $current_page == 'import-add.php' || $current_page == 'import-detail.php') echo 'active'; ?>">
        <img src="../assets/images/icons/import.png" alt="Nhập hàng">
        <span>Nhập hàng</span>
      </a>
      <a href="../admin/price.php" class="<?php if($current_page == 'price.php') echo 'active'; ?>"> 
        <img src="../assets/images/icons/dollar.png" alt="Giá bán">
        <span>Giá bán</span>
      </a>
      <a href="../admin/order-management.php" class="<?php if($current_page == 'order-management.php' || $current_page == 'order-detail.php') echo 'active'; ?>"> 
        <img src="../assets/images/icons/order-history.png" alt="Quản lý đơn đặt hàng">
        <span>Quản lý đơn đặt hàng</span>
      </a>
      <a href="../admin/inventory.php" class="<?php if($current_page == 'inventory.php') echo 'active'; ?>"> 
        <img src="../assets/images/icons/inventory-alt.png" alt="Quản lý tồn kho">
        <span>Quản lý tồn kho</span>
      </a>
      <div class="sidebar-footer">
        <a href="../index.html" class="logout-btn">
          <span>Đăng xuất</span>
        </a>
      </div>
  </nav>
</aside>