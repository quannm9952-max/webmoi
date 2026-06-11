-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Máy chủ: 127.0.0.1
-- Thời gian đã tạo: Th5 01, 2026 lúc 10:08 PM
-- Phiên bản máy phục vụ: 10.4.32-MariaDB
-- Phiên bản PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Cơ sở dữ liệu: `webbanhang`
--

DELIMITER $$
--
-- Thủ tục
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_DatHang` (IN `p_id_nguoi_dung` INT, IN `p_id_phuong_thuc` INT, IN `p_ten_nguoi_nhan` VARCHAR(150), IN `p_so_dien_thoai_nhan` VARCHAR(20), IN `p_dia_chi_giao_hang` TEXT, IN `p_ghi_chu` TEXT, OUT `p_id_don_hang` INT)   BEGIN
    DECLARE v_id_gio_hang INT DEFAULT NULL;
    DECLARE v_tong_tien DECIMAL(15,2) DEFAULT 0;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT id_gio_hang
    INTO v_id_gio_hang
    FROM gio_hang
    WHERE id_nguoi_dung = p_id_nguoi_dung
    LIMIT 1
    FOR UPDATE;

    IF v_id_gio_hang IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Giỏ hàng trống.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM chi_tiet_gio_hang
        WHERE id_gio_hang = v_id_gio_hang
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Giỏ hàng trống.';
    END IF;

    IF NOT EXISTS (
        SELECT 1
        FROM phuong_thuc_thanh_toan
        WHERE id_phuong_thuc = p_id_phuong_thuc
          AND trang_thai = 'hien'
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Phương thức thanh toán không hợp lệ.';
    END IF;

    IF EXISTS (
        SELECT 1
        FROM chi_tiet_gio_hang ct
        JOIN san_pham sp ON sp.id_san_pham = ct.id_san_pham
        WHERE ct.id_gio_hang = v_id_gio_hang
          AND (sp.trang_thai <> 'dang_ban' OR sp.so_luong_ton < ct.so_luong)
    ) THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Vượt quá số lượng tồn kho';
    END IF;

    SELECT COALESCE(SUM(ct.so_luong * ct.don_gia), 0)
    INTO v_tong_tien
    FROM chi_tiet_gio_hang ct
    WHERE ct.id_gio_hang = v_id_gio_hang;

    INSERT INTO don_hang (
        id_nguoi_dung,
        id_phuong_thuc,
        tong_tien,
        ten_nguoi_nhan,
        so_dien_thoai_nhan,
        dia_chi_giao_hang,
        ghi_chu
    )
    VALUES (
        p_id_nguoi_dung,
        p_id_phuong_thuc,
        v_tong_tien,
        p_ten_nguoi_nhan,
        p_so_dien_thoai_nhan,
        p_dia_chi_giao_hang,
        p_ghi_chu
    );

    SET p_id_don_hang = LAST_INSERT_ID();

    INSERT INTO chi_tiet_don_hang (
        id_don_hang,
        id_san_pham,
        so_luong,
        don_gia,
        thanh_tien
    )
    SELECT
        p_id_don_hang,
        ct.id_san_pham,
        ct.so_luong,
        ct.don_gia,
        ct.so_luong * ct.don_gia
    FROM chi_tiet_gio_hang ct
    WHERE ct.id_gio_hang = v_id_gio_hang;

    UPDATE san_pham sp
    JOIN chi_tiet_gio_hang ct ON ct.id_san_pham = sp.id_san_pham
    SET sp.so_luong_ton = sp.so_luong_ton - ct.so_luong,
        sp.trang_thai = CASE
            WHEN sp.so_luong_ton - ct.so_luong <= 0 THEN 'het_hang'
            ELSE sp.trang_thai
        END
    WHERE ct.id_gio_hang = v_id_gio_hang;

    INSERT INTO thanh_toan(id_don_hang, id_phuong_thuc, so_tien)
    VALUES(p_id_don_hang, p_id_phuong_thuc, v_tong_tien);

    INSERT INTO van_chuyen(id_don_hang, phi_van_chuyen)
    VALUES(p_id_don_hang, 0);

    DELETE FROM chi_tiet_gio_hang
    WHERE id_gio_hang = v_id_gio_hang;

    COMMIT;
END$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_HuyDonHang` (IN `p_id_don_hang` INT)   BEGIN
    DECLARE v_trang_thai VARCHAR(30) DEFAULT NULL;

    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;

    START TRANSACTION;

    SELECT trang_thai_don_hang
    INTO v_trang_thai
    FROM don_hang
    WHERE id_don_hang = p_id_don_hang
    LIMIT 1
    FOR UPDATE;

    IF v_trang_thai IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Không tìm thấy đơn hàng.';
    END IF;

    IF v_trang_thai = 'dang_giao' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Không thể hủy đơn hàng đang giao.';
    END IF;

    IF v_trang_thai = 'da_giao' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Không thể hủy đơn hàng đã giao.';
    END IF;

    IF v_trang_thai = 'da_huy' THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Đơn hàng đã được hủy trước đó.';
    END IF;

    UPDATE san_pham sp
    JOIN chi_tiet_don_hang ct ON ct.id_san_pham = sp.id_san_pham
    SET sp.so_luong_ton = sp.so_luong_ton + ct.so_luong,
        sp.trang_thai = CASE
            WHEN sp.trang_thai = 'het_hang' AND sp.so_luong_ton + ct.so_luong > 0 THEN 'dang_ban'
            ELSE sp.trang_thai
        END
    WHERE ct.id_don_hang = p_id_don_hang;

    UPDATE don_hang
    SET trang_thai_don_hang = 'da_huy'
    WHERE id_don_hang = p_id_don_hang;

    COMMIT;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_don_hang`
--

CREATE TABLE `chi_tiet_don_hang` (
  `id_chi_tiet` int(11) NOT NULL,
  `id_don_hang` int(11) NOT NULL,
  `id_san_pham` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL,
  `don_gia` decimal(15,2) NOT NULL,
  `thanh_tien` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_don_hang`
--

INSERT INTO `chi_tiet_don_hang` (`id_chi_tiet`, `id_don_hang`, `id_san_pham`, `so_luong`, `don_gia`, `thanh_tien`) VALUES
(1, 1, 4, 2, 790000.00, 1580000.00),
(2, 1, 6, 3, 990000.00, 2970000.00),
(4, 2, 7, 1, 8290000.00, 8290000.00),
(5, 3, 7, 1, 8290000.00, 8290000.00),
(6, 4, 7, 1, 8290000.00, 8290000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `chi_tiet_gio_hang`
--

CREATE TABLE `chi_tiet_gio_hang` (
  `id_gio_hang` int(11) NOT NULL,
  `id_san_pham` int(11) NOT NULL,
  `so_luong` int(11) NOT NULL DEFAULT 1,
  `don_gia` decimal(15,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `chi_tiet_gio_hang`
--

INSERT INTO `chi_tiet_gio_hang` (`id_gio_hang`, `id_san_pham`, `so_luong`, `don_gia`) VALUES
(2, 1, 1, 15990000.00);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_gia_san_pham`
--

CREATE TABLE `danh_gia_san_pham` (
  `id` int(11) NOT NULL,
  `id_nguoi_dung` int(11) DEFAULT NULL,
  `id_san_pham` int(11) DEFAULT NULL,
  `id_don_hang` int(11) DEFAULT NULL,
  `so_sao` int(11) DEFAULT NULL,
  `binh_luan` text DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp(),
  `trang_thai` enum('hien','an','cho_duyet') DEFAULT 'hien',
  `phan_hoi_admin` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_gia_san_pham`
--

INSERT INTO `danh_gia_san_pham` (`id`, `id_nguoi_dung`, `id_san_pham`, `id_don_hang`, `so_sao`, `binh_luan`, `ngay_tao`, `trang_thai`, `phan_hoi_admin`) VALUES
(1, 1, 4, 1, 5, 'ỔN', '2026-05-01 11:54:18', 'hien', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `danh_muc`
--

CREATE TABLE `danh_muc` (
  `id_danh_muc` int(11) NOT NULL,
  `ten_danh_muc` varchar(150) NOT NULL,
  `trang_thai` enum('hien','an') DEFAULT 'hien'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `danh_muc`
--

INSERT INTO `danh_muc` (`id_danh_muc`, `ten_danh_muc`, `trang_thai`) VALUES
(1, 'Laptop', 'hien'),
(2, 'Màn hình', 'hien'),
(3, 'Bàn phím', 'hien'),
(4, 'Chuột', 'hien'),
(5, 'SSD', 'hien'),
(6, 'Card đồ họa', 'hien');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `dat_lai_mat_khau`
--

CREATE TABLE `dat_lai_mat_khau` (
  `id_token` int(11) NOT NULL,
  `id_nguoi_dung` int(11) NOT NULL,
  `token_hash` varchar(255) NOT NULL,
  `het_han` datetime NOT NULL,
  `da_su_dung` tinyint(1) NOT NULL DEFAULT 0,
  `ngay_tao` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `don_hang`
--

CREATE TABLE `don_hang` (
  `id_don_hang` int(11) NOT NULL,
  `id_nguoi_dung` int(11) NOT NULL,
  `id_phuong_thuc` int(11) NOT NULL,
  `tong_tien` decimal(15,2) NOT NULL,
  `ten_nguoi_nhan` varchar(150) NOT NULL,
  `so_dien_thoai_nhan` varchar(20) NOT NULL,
  `dia_chi_giao_hang` text NOT NULL,
  `ghi_chu` text DEFAULT NULL,
  `trang_thai_don_hang` enum('cho_xac_nhan','da_xac_nhan','dang_giao','da_giao','da_huy') DEFAULT 'cho_xac_nhan',
  `ngay_dat` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `don_hang`
--

INSERT INTO `don_hang` (`id_don_hang`, `id_nguoi_dung`, `id_phuong_thuc`, `tong_tien`, `ten_nguoi_nhan`, `so_dien_thoai_nhan`, `dia_chi_giao_hang`, `ghi_chu`, `trang_thai_don_hang`, `ngay_dat`) VALUES
(1, 1, 1, 4550000.00, 'Admin TechShop', '0900000001', '5', NULL, 'da_giao', '2026-05-01 10:04:36'),
(2, 2, 1, 8290000.00, 'Nguyễn Văn A', '0900000002', '45', NULL, 'da_huy', '2026-05-01 10:16:23'),
(3, 2, 1, 8290000.00, 'Nguyễn Văn A', '0900000002', 'Quận 1, TP.HCM', NULL, 'da_huy', '2026-05-01 11:18:22'),
(4, 1, 1, 8290000.00, 'Admin TechShop', '0900000001', 'TP.HCM', NULL, 'da_huy', '2026-05-01 14:19:25');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `gio_hang`
--

CREATE TABLE `gio_hang` (
  `id_gio_hang` int(11) NOT NULL,
  `id_nguoi_dung` int(11) NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `gio_hang`
--

INSERT INTO `gio_hang` (`id_gio_hang`, `id_nguoi_dung`, `ngay_tao`) VALUES
(1, 1, '2026-05-01 06:13:40'),
(2, 3, '2026-05-01 06:19:53'),
(3, 2, '2026-05-01 06:27:28');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `khuyen_mai`
--

CREATE TABLE `khuyen_mai` (
  `id_khuyen_mai` int(11) NOT NULL,
  `ten_khuyen_mai` varchar(150) NOT NULL,
  `phan_tram_giam` decimal(5,2) NOT NULL,
  `ngay_bat_dau` datetime NOT NULL,
  `ngay_ket_thuc` datetime NOT NULL,
  `trang_thai` enum('dang_dien_ra','ket_thuc','an') DEFAULT 'dang_dien_ra'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `khuyen_mai`
--

INSERT INTO `khuyen_mai` (`id_khuyen_mai`, `ten_khuyen_mai`, `phan_tram_giam`, `ngay_bat_dau`, `ngay_ket_thuc`, `trang_thai`) VALUES
(1, 'Mega Sale', 10.00, '2026-04-30 13:10:58', '2026-05-31 13:10:58', 'dang_dien_ra');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `nguoi_dung`
--

CREATE TABLE `nguoi_dung` (
  `id_nguoi_dung` int(11) NOT NULL,
  `id_vai_tro` int(11) NOT NULL,
  `ho_ten` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `so_dien_thoai` varchar(20) DEFAULT NULL,
  `mat_khau` varchar(255) NOT NULL,
  `dia_chi` text DEFAULT NULL,
  `trang_thai` enum('hoat_dong','khoa') DEFAULT 'hoat_dong',
  `provider` varchar(30) DEFAULT 'local',
  `google_id` varchar(255) DEFAULT NULL,
  `avatar_url` varchar(500) DEFAULT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `nguoi_dung`
--

INSERT INTO `nguoi_dung` (`id_nguoi_dung`, `id_vai_tro`, `ho_ten`, `email`, `so_dien_thoai`, `mat_khau`, `dia_chi`, `trang_thai`, `provider`, `google_id`, `avatar_url`, `ngay_tao`) VALUES
(1, 1, 'Admin TechShop', 'admin@techshop.vn', '0900000001', '$2y$12$VblHMYXv9R1rYk241GgBieLc3pgY4WyYYcSiUp7UHWe3Pjr4y.zha', 'TP.HCM', 'hoat_dong', 'local', NULL, NULL, '2026-05-01 06:10:58'),
(2, 2, 'Nguyễn Văn A', 'customer@techshop.vn', '0900000002', '$2y$12$VblHMYXv9R1rYk241GgBieLc3pgY4WyYYcSiUp7UHWe3Pjr4y.zha', 'Quận 1, TP.HCM', 'hoat_dong', 'local', NULL, NULL, '2026-05-01 06:10:58'),
(3, 2, 'đoàn thị kim phượng', 'doanp7293@gmail.com', '0383825425', '$2y$10$.TZsQKE/Nup4Q75THC8Fyu6yKzt5UygAMD/ycd05LTzSl4mwV.9WG', '35', 'hoat_dong', 'local', NULL, NULL, '2026-05-01 06:19:46');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `phuong_thuc_thanh_toan`
--

CREATE TABLE `phuong_thuc_thanh_toan` (
  `id_phuong_thuc` int(11) NOT NULL,
  `ten_phuong_thuc` varchar(150) NOT NULL,
  `mo_ta` text DEFAULT NULL,
  `trang_thai` enum('hien','an') DEFAULT 'hien'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `phuong_thuc_thanh_toan`
--

INSERT INTO `phuong_thuc_thanh_toan` (`id_phuong_thuc`, `ten_phuong_thuc`, `mo_ta`, `trang_thai`) VALUES
(1, 'Thanh toán khi nhận hàng', 'Khách thanh toán trực tiếp khi nhận hàng', 'hien'),
(2, 'Chuyển khoản ngân hàng', 'Chuyển khoản trước khi giao hàng', 'hien');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham`
--

CREATE TABLE `san_pham` (
  `id_san_pham` int(11) NOT NULL,
  `id_danh_muc` int(11) NOT NULL,
  `id_thuong_hieu` int(11) NOT NULL,
  `ten_san_pham` varchar(255) NOT NULL,
  `ma_san_pham` varchar(80) NOT NULL,
  `gia` decimal(15,2) NOT NULL,
  `hinh_anh_chinh` varchar(255) DEFAULT NULL,
  `mo_ta_ngan` text DEFAULT NULL,
  `mo_ta_chi_tiet` longtext DEFAULT NULL,
  `so_luong_ton` int(11) DEFAULT 0,
  `trang_thai` enum('dang_ban','het_hang','an') DEFAULT 'dang_ban',
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `san_pham`
--

INSERT INTO `san_pham` (`id_san_pham`, `id_danh_muc`, `id_thuong_hieu`, `ten_san_pham`, `ma_san_pham`, `gia`, `hinh_anh_chinh`, `mo_ta_ngan`, `mo_ta_chi_tiet`, `so_luong_ton`, `trang_thai`, `ngay_tao`) VALUES
(1, 1, 1, 'Dell Inspiron 15', 'LAP-DELL-001', 15990000.00, 'https://res.cloudinary.com/daro9erbh/image/upload/c_pad,w_400,h_400,b_white/v1776581907/dell-inspiron-15-3530-i7-n5i7301w1-thumb-638754980409982405-600x600_oebsy3.jpg', 'Laptop văn phòng ổn định', 'Phù hợp học tập, văn phòng', 20, 'dang_ban', '2026-05-01 06:10:58'),
(2, 1, 2, 'Asus Vivobook 14', 'LAP-ASUS-001', 13990000.00, 'https://res.cloudinary.com/daro9erbh/image/upload/c_pad,w_400,h_400,b_white/v1776581997/maxresdefault_czcab1.jpg', 'Laptop mỏng nhẹ', 'Phù hợp sinh viên', 18, 'dang_ban', '2026-05-01 06:10:58'),
(3, 2, 4, 'Samsung Monitor 24 inch', 'MON-SAM-001', 3290000.00, 'https://res.cloudinary.com/daro9erbh/image/upload/c_pad,w_400,h_400,b_white/v1776582168/samsung-s3-s31c-ls27c310eaexxv-27-inch-fhd-thumb-1-600x600_xavytv.jpg', 'Màn hình Full HD', 'Làm việc và giải trí', 30, 'dang_ban', '2026-05-01 06:10:58'),
(4, 3, 3, 'Logitech K380', 'KEY-LOG-001', 790000.00, 'https://res.cloudinary.com/daro9erbh/image/upload/c_pad,w_400,h_400,b_white/v1776582187/logitech-g-pro-x-mechanical-gaming-keyboard_2-600x400_0472fe2fcf3640dd8836c1fed7377043_m5i5fq.jpg', 'Bàn phím bluetooth', 'Gọn nhẹ', 38, 'dang_ban', '2026-05-01 06:10:58'),
(5, 4, 3, 'Logitech G102', 'MOU-LOG-001', 390000.00, 'https://res.cloudinary.com/daro9erbh/image/upload/c_pad,w_400,h_400,b_white/v1776582301/chuot-gaming-logitech-g102-gen2-lightsync-den-1-750x500_dtp0kf.jpg', 'Chuột gaming', 'Phổ thông', 55, 'dang_ban', '2026-05-01 06:10:58'),
(6, 5, 6, 'Kingston NV2 500GB', 'SSD-KIN-001', 990000.00, 'https://res.cloudinary.com/daro9erbh/image/upload/c_pad,w_400,h_400,b_white/v1776582684/ktc-product-ssd-snv2s-250g-3-lg_134f63eaef554cb0984a5d322dc25cb5_8ef94ccd223940788600ff0acd438e2b_gu0fhk.png', 'SSD NVMe', 'Tốc độ cao', 25, 'dang_ban', '2026-05-01 06:10:58'),
(7, 6, 5, 'MSI RTX 4060 Ventus', 'GPU-MSI-001', 8290000.00, 'https://res.cloudinary.com/daro9erbh/image/upload/c_pad,w_400,h_400,b_white/v1776582501/vga-asus-dual-geforce-rtx-4060-oc-edition-8gb-gddr6-dual-rtx4060-o8g_cmhlft.jpg', 'Card đồ họa RTX', 'Gaming 1080p', 10, 'dang_ban', '2026-05-01 06:10:58');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham_khuyen_mai`
--

CREATE TABLE `san_pham_khuyen_mai` (
  `id_san_pham` int(11) NOT NULL,
  `id_khuyen_mai` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `san_pham_khuyen_mai`
--

INSERT INTO `san_pham_khuyen_mai` (`id_san_pham`, `id_khuyen_mai`) VALUES
(1, 1),
(3, 1),
(7, 1);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `san_pham_yeu_thich`
--

CREATE TABLE `san_pham_yeu_thich` (
  `id_nguoi_dung` int(11) NOT NULL,
  `id_san_pham` int(11) NOT NULL,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thanh_toan`
--

CREATE TABLE `thanh_toan` (
  `id_thanh_toan` int(11) NOT NULL,
  `id_don_hang` int(11) NOT NULL,
  `id_phuong_thuc` int(11) NOT NULL,
  `so_tien` decimal(15,2) NOT NULL,
  `trang_thai_thanh_toan` enum('chua_thanh_toan','da_thanh_toan','that_bai') DEFAULT 'chua_thanh_toan',
  `ngay_thanh_toan` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thanh_toan`
--

INSERT INTO `thanh_toan` (`id_thanh_toan`, `id_don_hang`, `id_phuong_thuc`, `so_tien`, `trang_thai_thanh_toan`, `ngay_thanh_toan`) VALUES
(1, 1, 1, 4550000.00, 'chua_thanh_toan', NULL),
(2, 2, 1, 8290000.00, 'chua_thanh_toan', NULL),
(3, 3, 1, 8290000.00, 'chua_thanh_toan', NULL),
(4, 4, 1, 8290000.00, 'chua_thanh_toan', NULL);

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `thuong_hieu`
--

CREATE TABLE `thuong_hieu` (
  `id_thuong_hieu` int(11) NOT NULL,
  `ten_thuong_hieu` varchar(150) NOT NULL,
  `trang_thai` enum('hien','an') DEFAULT 'hien'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `thuong_hieu`
--

INSERT INTO `thuong_hieu` (`id_thuong_hieu`, `ten_thuong_hieu`, `trang_thai`) VALUES
(1, 'Dell', 'hien'),
(2, 'Asus', 'hien'),
(3, 'Logitech', 'hien'),
(4, 'Samsung', 'hien'),
(5, 'MSI', 'hien'),
(6, 'Kingston', 'hien');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tin_nhan`
--

CREATE TABLE `tin_nhan` (
  `id` int(11) NOT NULL,
  `id_nguoi_gui` int(11) DEFAULT NULL,
  `id_nguoi_nhan` int(11) DEFAULT NULL,
  `noi_dung` text DEFAULT NULL,
  `la_admin` tinyint(1) DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `tin_nhan_ho_tro`
--

CREATE TABLE `tin_nhan_ho_tro` (
  `id_tin_nhan` int(11) NOT NULL,
  `id_nguoi_dung` int(11) NOT NULL,
  `nguoi_gui` varchar(20) DEFAULT 'khach',
  `noi_dung` text NOT NULL,
  `da_doc` tinyint(1) DEFAULT 0,
  `ngay_tao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Đang đổ dữ liệu cho bảng `tin_nhan_ho_tro`
--

INSERT INTO `tin_nhan_ho_tro` (`id_tin_nhan`, `id_nguoi_dung`, `nguoi_gui`, `noi_dung`, `da_doc`, `ngay_tao`) VALUES
(1, 1, 'khach', 'hello sốp', 1, '2026-05-01 12:19:37'),
(2, 1, 'admin', 'jz má', 0, '2026-05-01 12:19:45'),
(3, 1, 'khach', 'ủa được mà ta', 1, '2026-05-01 15:02:02');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `vai_tro`
--

CREATE TABLE `vai_tro` (
  `id_vai_tro` int(11) NOT NULL,
  `ten_vai_tro` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `vai_tro`
--

INSERT INTO `vai_tro` (`id_vai_tro`, `ten_vai_tro`) VALUES
(1, 'admin'),
(2, 'customer');

-- --------------------------------------------------------

--
-- Cấu trúc bảng cho bảng `van_chuyen`
--

CREATE TABLE `van_chuyen` (
  `id_van_chuyen` int(11) NOT NULL,
  `id_don_hang` int(11) NOT NULL,
  `don_vi_van_chuyen` varchar(150) DEFAULT NULL,
  `phi_van_chuyen` decimal(15,2) DEFAULT 0.00,
  `ma_van_don` varchar(150) DEFAULT NULL,
  `trang_thai_giao_hang` enum('chuan_bi_hang','dang_giao','da_giao','that_bai') DEFAULT 'chuan_bi_hang'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Đang đổ dữ liệu cho bảng `van_chuyen`
--

INSERT INTO `van_chuyen` (`id_van_chuyen`, `id_don_hang`, `don_vi_van_chuyen`, `phi_van_chuyen`, `ma_van_don`, `trang_thai_giao_hang`) VALUES
(1, 1, NULL, 0.00, NULL, 'chuan_bi_hang'),
(2, 2, NULL, 0.00, NULL, 'chuan_bi_hang'),
(3, 3, NULL, 0.00, NULL, 'chuan_bi_hang'),
(4, 4, NULL, 0.00, NULL, 'chuan_bi_hang');

--
-- Chỉ mục cho các bảng đã đổ
--

--
-- Chỉ mục cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  ADD PRIMARY KEY (`id_chi_tiet`),
  ADD KEY `id_don_hang` (`id_don_hang`),
  ADD KEY `id_san_pham` (`id_san_pham`);

--
-- Chỉ mục cho bảng `chi_tiet_gio_hang`
--
ALTER TABLE `chi_tiet_gio_hang`
  ADD PRIMARY KEY (`id_gio_hang`,`id_san_pham`),
  ADD KEY `id_san_pham` (`id_san_pham`);

--
-- Chỉ mục cho bảng `danh_gia_san_pham`
--
ALTER TABLE `danh_gia_san_pham`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  ADD PRIMARY KEY (`id_danh_muc`);

--
-- Chỉ mục cho bảng `dat_lai_mat_khau`
--
ALTER TABLE `dat_lai_mat_khau`
  ADD PRIMARY KEY (`id_token`),
  ADD KEY `idx_token_hash` (`token_hash`),
  ADD KEY `idx_user_expire` (`id_nguoi_dung`,`het_han`);

--
-- Chỉ mục cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  ADD PRIMARY KEY (`id_don_hang`),
  ADD KEY `id_nguoi_dung` (`id_nguoi_dung`),
  ADD KEY `id_phuong_thuc` (`id_phuong_thuc`);

--
-- Chỉ mục cho bảng `gio_hang`
--
ALTER TABLE `gio_hang`
  ADD PRIMARY KEY (`id_gio_hang`),
  ADD UNIQUE KEY `id_nguoi_dung` (`id_nguoi_dung`);

--
-- Chỉ mục cho bảng `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  ADD PRIMARY KEY (`id_khuyen_mai`);

--
-- Chỉ mục cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD PRIMARY KEY (`id_nguoi_dung`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `google_id` (`google_id`),
  ADD KEY `id_vai_tro` (`id_vai_tro`);

--
-- Chỉ mục cho bảng `phuong_thuc_thanh_toan`
--
ALTER TABLE `phuong_thuc_thanh_toan`
  ADD PRIMARY KEY (`id_phuong_thuc`);

--
-- Chỉ mục cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD PRIMARY KEY (`id_san_pham`),
  ADD UNIQUE KEY `ma_san_pham` (`ma_san_pham`),
  ADD KEY `id_danh_muc` (`id_danh_muc`),
  ADD KEY `id_thuong_hieu` (`id_thuong_hieu`);

--
-- Chỉ mục cho bảng `san_pham_khuyen_mai`
--
ALTER TABLE `san_pham_khuyen_mai`
  ADD PRIMARY KEY (`id_san_pham`,`id_khuyen_mai`),
  ADD KEY `id_khuyen_mai` (`id_khuyen_mai`);

--
-- Chỉ mục cho bảng `san_pham_yeu_thich`
--
ALTER TABLE `san_pham_yeu_thich`
  ADD PRIMARY KEY (`id_nguoi_dung`,`id_san_pham`),
  ADD KEY `id_san_pham` (`id_san_pham`);

--
-- Chỉ mục cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD PRIMARY KEY (`id_thanh_toan`),
  ADD KEY `id_don_hang` (`id_don_hang`),
  ADD KEY `id_phuong_thuc` (`id_phuong_thuc`);

--
-- Chỉ mục cho bảng `thuong_hieu`
--
ALTER TABLE `thuong_hieu`
  ADD PRIMARY KEY (`id_thuong_hieu`);

--
-- Chỉ mục cho bảng `tin_nhan`
--
ALTER TABLE `tin_nhan`
  ADD PRIMARY KEY (`id`);

--
-- Chỉ mục cho bảng `tin_nhan_ho_tro`
--
ALTER TABLE `tin_nhan_ho_tro`
  ADD PRIMARY KEY (`id_tin_nhan`);

--
-- Chỉ mục cho bảng `vai_tro`
--
ALTER TABLE `vai_tro`
  ADD PRIMARY KEY (`id_vai_tro`),
  ADD UNIQUE KEY `ten_vai_tro` (`ten_vai_tro`);

--
-- Chỉ mục cho bảng `van_chuyen`
--
ALTER TABLE `van_chuyen`
  ADD PRIMARY KEY (`id_van_chuyen`),
  ADD KEY `id_don_hang` (`id_don_hang`);

--
-- AUTO_INCREMENT cho các bảng đã đổ
--

--
-- AUTO_INCREMENT cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  MODIFY `id_chi_tiet` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `danh_gia_san_pham`
--
ALTER TABLE `danh_gia_san_pham`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `danh_muc`
--
ALTER TABLE `danh_muc`
  MODIFY `id_danh_muc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `dat_lai_mat_khau`
--
ALTER TABLE `dat_lai_mat_khau`
  MODIFY `id_token` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  MODIFY `id_don_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `gio_hang`
--
ALTER TABLE `gio_hang`
  MODIFY `id_gio_hang` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `khuyen_mai`
--
ALTER TABLE `khuyen_mai`
  MODIFY `id_khuyen_mai` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  MODIFY `id_nguoi_dung` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `phuong_thuc_thanh_toan`
--
ALTER TABLE `phuong_thuc_thanh_toan`
  MODIFY `id_phuong_thuc` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  MODIFY `id_san_pham` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  MODIFY `id_thanh_toan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT cho bảng `thuong_hieu`
--
ALTER TABLE `thuong_hieu`
  MODIFY `id_thuong_hieu` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT cho bảng `tin_nhan`
--
ALTER TABLE `tin_nhan`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT cho bảng `tin_nhan_ho_tro`
--
ALTER TABLE `tin_nhan_ho_tro`
  MODIFY `id_tin_nhan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT cho bảng `vai_tro`
--
ALTER TABLE `vai_tro`
  MODIFY `id_vai_tro` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT cho bảng `van_chuyen`
--
ALTER TABLE `van_chuyen`
  MODIFY `id_van_chuyen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Các ràng buộc cho các bảng đã đổ
--

--
-- Các ràng buộc cho bảng `chi_tiet_don_hang`
--
ALTER TABLE `chi_tiet_don_hang`
  ADD CONSTRAINT `chi_tiet_don_hang_ibfk_1` FOREIGN KEY (`id_don_hang`) REFERENCES `don_hang` (`id_don_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_don_hang_ibfk_2` FOREIGN KEY (`id_san_pham`) REFERENCES `san_pham` (`id_san_pham`);

--
-- Các ràng buộc cho bảng `chi_tiet_gio_hang`
--
ALTER TABLE `chi_tiet_gio_hang`
  ADD CONSTRAINT `chi_tiet_gio_hang_ibfk_1` FOREIGN KEY (`id_gio_hang`) REFERENCES `gio_hang` (`id_gio_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `chi_tiet_gio_hang_ibfk_2` FOREIGN KEY (`id_san_pham`) REFERENCES `san_pham` (`id_san_pham`);

--
-- Các ràng buộc cho bảng `dat_lai_mat_khau`
--
ALTER TABLE `dat_lai_mat_khau`
  ADD CONSTRAINT `fk_reset_user` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `don_hang`
--
ALTER TABLE `don_hang`
  ADD CONSTRAINT `don_hang_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id_nguoi_dung`),
  ADD CONSTRAINT `don_hang_ibfk_2` FOREIGN KEY (`id_phuong_thuc`) REFERENCES `phuong_thuc_thanh_toan` (`id_phuong_thuc`);

--
-- Các ràng buộc cho bảng `gio_hang`
--
ALTER TABLE `gio_hang`
  ADD CONSTRAINT `gio_hang_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id_nguoi_dung`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `nguoi_dung`
--
ALTER TABLE `nguoi_dung`
  ADD CONSTRAINT `nguoi_dung_ibfk_1` FOREIGN KEY (`id_vai_tro`) REFERENCES `vai_tro` (`id_vai_tro`);

--
-- Các ràng buộc cho bảng `san_pham`
--
ALTER TABLE `san_pham`
  ADD CONSTRAINT `san_pham_ibfk_1` FOREIGN KEY (`id_danh_muc`) REFERENCES `danh_muc` (`id_danh_muc`),
  ADD CONSTRAINT `san_pham_ibfk_2` FOREIGN KEY (`id_thuong_hieu`) REFERENCES `thuong_hieu` (`id_thuong_hieu`);

--
-- Các ràng buộc cho bảng `san_pham_khuyen_mai`
--
ALTER TABLE `san_pham_khuyen_mai`
  ADD CONSTRAINT `san_pham_khuyen_mai_ibfk_1` FOREIGN KEY (`id_san_pham`) REFERENCES `san_pham` (`id_san_pham`) ON DELETE CASCADE,
  ADD CONSTRAINT `san_pham_khuyen_mai_ibfk_2` FOREIGN KEY (`id_khuyen_mai`) REFERENCES `khuyen_mai` (`id_khuyen_mai`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `san_pham_yeu_thich`
--
ALTER TABLE `san_pham_yeu_thich`
  ADD CONSTRAINT `san_pham_yeu_thich_ibfk_1` FOREIGN KEY (`id_nguoi_dung`) REFERENCES `nguoi_dung` (`id_nguoi_dung`) ON DELETE CASCADE,
  ADD CONSTRAINT `san_pham_yeu_thich_ibfk_2` FOREIGN KEY (`id_san_pham`) REFERENCES `san_pham` (`id_san_pham`) ON DELETE CASCADE;

--
-- Các ràng buộc cho bảng `thanh_toan`
--
ALTER TABLE `thanh_toan`
  ADD CONSTRAINT `thanh_toan_ibfk_1` FOREIGN KEY (`id_don_hang`) REFERENCES `don_hang` (`id_don_hang`) ON DELETE CASCADE,
  ADD CONSTRAINT `thanh_toan_ibfk_2` FOREIGN KEY (`id_phuong_thuc`) REFERENCES `phuong_thuc_thanh_toan` (`id_phuong_thuc`);

--
-- Các ràng buộc cho bảng `van_chuyen`
--
ALTER TABLE `van_chuyen`
  ADD CONSTRAINT `van_chuyen_ibfk_1` FOREIGN KEY (`id_don_hang`) REFERENCES `don_hang` (`id_don_hang`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
