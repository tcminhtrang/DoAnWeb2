/* ==========================================================
   1. CÁC HÀM TIỆN ÍCH & THÔNG BÁO
   ========================================================== */
function showToast(message, type = "success") {

    alert(message); 
}

/* ==========================================================
   2. DỮ LIỆU ĐỊA CHÍNH (TỈNH/THÀNH - QUẬN/HUYỆN)
   ========================================================== */
const districtData = {
    "Hồ Chí Minh": ["Quận 1", "Quận 3", "Quận 10", "Bình Thạnh", "TP. Thủ Đức"],
    "Hà Nội": ["Quận Ba Đình", "Quận Cầu Giấy", "Quận Đống Đa"],
    "Đà Nẵng": ["Quận Hải Châu", "Quận Thanh Khê"],
    "Cần Thơ": ["Quận Ninh Kiều", "Quận Cái Răng"],
    "Hải Phòng": ["Quận Hồng Bàng", "Quận Lê Chân"],
    "Bình Dương": ["TP. Thủ Dầu Một", "TP. Thuận An"]
};

function updateDistricts() {
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    if (!citySelect || !districtSelect) return;
    
    const selectedCity = citySelect.value;
    districtSelect.innerHTML = '<option value="">Chọn Quận</option>';

    if (selectedCity && districtData[selectedCity]) {
        districtData[selectedCity].forEach(district => {
            const option = document.createElement('option');
            option.value = district;
            option.innerText = district;
            districtSelect.appendChild(option);
        });
    }
}

/* ==========================================================
   3. LOGIC KHỞI TẠO TẤT CẢ SỰ KIỆN (DOM CONTENT LOADED)
   ========================================================== */
document.addEventListener('DOMContentLoaded', function() {
    
    // --- A. XỬ LÝ TĂNG GIẢM SỐ LƯỢNG (Chi tiết & Giỏ hàng) ---
    const qtyInput = document.getElementById('quantity');
    const btnQtys = document.querySelectorAll('.btn-qty');

    if (qtyInput && btnQtys.length > 0) {
        btnQtys.forEach(btn => {
            btn.onclick = function() {
                let currentQty = parseInt(qtyInput.value) || 1;
                const type = this.getAttribute('data-type');
                const maxStock = parseInt(this.getAttribute('data-max')) || 99;

                if (type === 'plus') {
                    if (currentQty < maxStock) {
                        qtyInput.value = currentQty + 1;
                    } else {
                        showToast("Rất tiếc, chỉ còn " + maxStock + " sản phẩm!", "error");
                    }
                } else if (type === 'minus' && currentQty > 1) {
                    qtyInput.value = currentQty - 1;
                }
            };
        });
    }

    // --- B. XỬ LÝ THÊM VÀO GIỎ HÀNG (AJAX) ---
    // Sửa lại hàm addToCart trong main.js
// --- B. XỬ LÝ THÊM VÀO GIỎ HÀNG (AJAX) ---
function addToCart(productId, quantity) {
    if (typeof isLoggedIn !== 'undefined' && !isLoggedIn) {
        alert("Vui lòng đăng nhập để đặt món!");
        window.location.href = "Dangnhap.php";
        return;
    }

    fetch('process_cart.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `action=add&product_id=${productId}&quantity=${quantity}`
    })
    .then(res => res.json()) // Đổi .text() thành .json() để đọc đúng cấu trúc file PHP trả về
    .then(data => {
        if (data.status === 'success') {
            alert("Đã thêm món vào giỏ hàng của Chicken Joy!");
            location.reload(); 
        } else {
            // Đây là lúc nó hiện ra lỗi thật (Ví dụ: Lỗi Database hoặc Chưa đăng nhập)
            alert("Lỗi: " + data.message);
        }
    })
    .catch(err => {
        console.error('Lỗi:', err);
        alert("Có lỗi xảy ra khi kết nối với máy chủ!");
    });
}

    const btnDetail = document.getElementById('addToCartBtn');
    if (btnDetail) {
        btnDetail.onclick = function() {
            const productId = this.getAttribute('data-id');
            const quantity = document.getElementById('quantity') ? document.getElementById('quantity').value : 1;
            addToCart(productId, quantity);
        };
    }

    const btnsMenu = document.querySelectorAll('.btn-add-to-cart');
    btnsMenu.forEach(btn => {
        btn.onclick = function() {
            const productId = this.getAttribute('data-id');
            addToCart(productId, 1);
        };
    });

    // --- C. HIỆU ỨNG PLACEHOLDER Ô TÌM KIẾM ---
    const searchInput = document.querySelector('input[name="search"]');
    if (searchInput) {
        const placeholders = ["Tìm món ăn...", "Thử: 'Mì ý'", "Gõ '50000' lọc giá"];
        let pIndex = 0;
        setInterval(() => {
            searchInput.placeholder = placeholders[pIndex];
            pIndex = (pIndex + 1) % placeholders.length;
        }, 3000);
    }

    // --- D. LOGIC THANH TOÁN (ẨN HIỆN FORM & VALIDATION) ---
    const addrRadios = document.querySelectorAll('input[name="address_type"]');
    const newAddressFields = document.getElementById('new-address-fields');
    
    if (addrRadios.length > 0 && newAddressFields) {
        addrRadios.forEach(radio => {
            radio.addEventListener('change', function() {
                const inputs = newAddressFields.querySelectorAll('input, select, textarea');
                if (this.value === 'new') {
                    newAddressFields.style.display = 'block';
                    inputs.forEach(input => input.setAttribute('required', ''));
                } else {
                    newAddressFields.style.display = 'none';
                    inputs.forEach(input => input.removeAttribute('required'));
                }
            });
        });
    }

    // Kiểm tra dữ liệu trước khi gửi Form Thanh toán
    const checkoutForm = document.querySelector('form[action*="process_checkout.php"]');
    if (checkoutForm) {
        checkoutForm.addEventListener('submit', function(e) {
            const selectedAddress = document.querySelector('input[name="address_type"]:checked');
            
            if (selectedAddress && selectedAddress.value === 'new') {
                const name = document.getElementsByName('new_name')[0]?.value.trim();
                const phone = document.getElementsByName('new_phone')[0]?.value.trim();
                const specific = document.getElementsByName('specific_address')[0]?.value.trim();
                const district = document.getElementById('district')?.value; // Lấy theo ID
                const city = document.getElementById('city')?.value; // Lấy theo ID

                if (!name || !phone || !specific || !district || !city) {
                    e.preventDefault(); 
                    alert(" Vuii lòng điền đầy đủ tất cả các ô ở phần địa chỉ mới nhé!");
                }
            }
        });
    }

    // --- E. XỬ LÝ CẬP NHẬT/XÓA Ở TRANG GIỎ HÀNG ---
    document.querySelectorAll('.update-qty, .delete-item').forEach(el => {
        el.onclick = function() {
            let action = this.classList.contains('delete-item') ? 'delete' : this.dataset.action;
            let productId = this.dataset.id;

            if (action === 'delete' && !confirm("Bạn muốn xóa món này khỏi giỏ hàng?")) return;

            fetch('process_cart.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: `action=${action}&product_id=${productId}`
            })
            .then(res => {
                if(res.ok) {
                    location.reload(); 
                } else {
                    alert("Lỗi hệ thống!");
                }
            })
            .catch(err => console.error('Lỗi:', err));
        };
    });

    const clearBtn = document.querySelector('.clear-cart');
    if (clearBtn) {
        clearBtn.onclick = function(e) {
            e.preventDefault(); 
            if (confirm("Bạn có chắc muốn xóa TOÀN BỘ giỏ hàng của Chicken Joy?")) {
                fetch('process_cart.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=clear&product_id=0`
                })
                .then(() => location.reload())
                .catch(err => console.error('Lỗi:', err));
            }
        };
    }

    // Tìm kiếm đơn hàng theo Mã đơn (#DH001...)
    const orderSearch = document.getElementById('order-search');
    if (orderSearch) {
        orderSearch.addEventListener('keyup', function() {
            let searchText = this.value.toLowerCase();
            let orderCards = document.querySelectorAll('.order-card');
            
            orderCards.forEach(card => {
                let orderId = card.querySelector('.order-id').innerText.toLowerCase();
                // Nếu mã đơn khớp với nội dung tìm kiếm thì hiện, không thì ẩn
                if (orderId.includes(searchText)) {
                    card.style.display = "block";
                } else {
                    card.style.display = "none";
                }
            });
        });
    }
});

/* ==========================================================
   XỬ LÝ TÌM KIẾM & LỌC TRẠNG THÁI ĐƠN HÀNG
   ========================================================== */
document.addEventListener('DOMContentLoaded', function() {
    const filterSelect = document.getElementById('order-filter');
    const searchInput = document.getElementById('order-search');
    const searchBtn = document.getElementById('btn-search');

    // Hàm điều hướng trang kèm theo các tham số lọc và tìm kiếm
    function reloadWithParams() {
        const filterVal = filterSelect.value;
        const searchVal = searchInput.value.trim();
        
        // Luôn quay về trang 1 khi thực hiện lọc hoặc tìm kiếm mới
        let url = `Donhang.php?page=1&filter=${filterVal}`;
        
        if (searchVal !== "") {
            // encodeURIComponent để xử lý các ký tự đặc biệt trong ô tìm kiếm
            url += `&search=${encodeURIComponent(searchVal)}`;
        }
        
        window.location.href = url;
    }

    // 1. Sự kiện khi thay đổi lựa chọn trong Select Box (Lọc trạng thái)
    if (filterSelect) {
        filterSelect.addEventListener('change', reloadWithParams);
    }

    // 2. Sự kiện khi nhấn nút Kính lúp (Tìm kiếm)
    if (searchBtn) {
        searchBtn.addEventListener('click', function(e) {
            e.preventDefault();
            reloadWithParams();
        });
    }

    // 3. Sự kiện khi nhấn phím Enter trong ô nhập mã đơn hàng
    if (searchInput) {
        searchInput.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                reloadWithParams();
            }
        });
    }
});
