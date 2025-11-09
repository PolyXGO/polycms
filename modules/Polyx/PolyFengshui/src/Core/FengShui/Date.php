<?php

namespace Modules\Polyx\PolyFengshui\Core\FengShui;

use App\Support\OptionRepository;

class Date
{
    private static ?Date $instance = null;
    protected static bool $is_fee = true;

    // Tách hết các thuộc tính sang các class độc lập riêng. Các phần tính toán liên quan cũng độc lập và gọi các class phù hợp để dễ phát triển.

    public static $DAYS_EN;
    public static $DAYS_VI;
    public static $MONTH_EN;
    public static $MONTH_VI;
    public static $TUAN;
    public static $THANG;
    public static $CAN;
    public static $CHI;
    public static $GIO_HD;
    public static $TIETKHI;
    public static $TIET12;
    public static $TIET12KHI12;
    public static $TIET_KHI_DEGREE;
    public static $TANGCAN_DIACHI;
    public static $CANNGUHANH;
    public static $CHINGUHANH;
    public static $PI;
    public static $TK13;
    public static $TK14;
    public static $TK15;
    public static $TK16;
    public static $TK17;
    public static $TK18;
    public static $TK19;
    public static $TK20;
    public static $TK21;
    public static $TK22;
    public static $FIRST_DAY;
    public static $LAST_DAY;
    //COMPASS
    public static $arrCompassCung;
    public static $QUEBATQUAI;
    public static $arrNguHanhCung;
    public static $SinhKhi;
    public static $ThienY;
    public static $DienNien;
    public static $PhucVi;
    public static $HoaHai;
    public static $NguQuy;
    public static $LucSat;
    public static $TuyetMenh;
    //THẤT SÁT
    public static $ThienHyYear;
    public static $ThienHyMonth;
    public static $KiepSat;
    public static $HongLoan;
    public static $QuyNhan;
    public static $VanXuong;
    public static $HocDuong;
    //THẬP THẦN
    public static $THAPTHAN;
    public static $THAPTHANKEY;
    public static $THAPTHAN_GIAP;
    public static $THAPTHAN_AT;
    public static $THAPTHAN_BINH;
    public static $THAPTHAN_DINH;
    public static $THAPTHAN_MAU;
    public static $THAPTHAN_KY;
    public static $THAPTHAN_CANH;
    public static $THAPTHAN_TAN;
    public static $THAPTHAN_NHAM;
    public static $THAPTHAN_QUY;
    //TODO: Thập Nhị Trực
    public static $THAPNHITRUC;
    public static $THAPNHITRUCTHANG;
    //TODO: Kiết Hung Nhật
    public static $KIETHUNGNHAT;
    public static $KIETHUNGNHATTOTXAU;
    public static $KIETHUNGNGAY;
    public static $NGAYHOANGDAO;
    public static $NGAYHACDAO;
    //CUUCUNGPHITINH
    public static $CUUCUNGPHITINH;
    public static $LUONGTHIENXICH;
    public static $LUONGTHIENXICHTABLE;
    public static $TIETKHI_CUUTINH;
    public static $CUUTINH_NGAY_THEO_TIETKHI;
    public static $DAYCANCHI_LUCTHAP_HOAGIAP;
    public static $CUUTINH_HOUR_CHI_DAY;
    public static $GIOCUUTINH_CHI;
    public static $TIETKHI_CUUTINH_HOUR;
    public static $CUUTINHMONTH;
    public static $CUUTINHMONTHYEAR;
    public static $TAMSATCHI;
    public static $TAMSAT;
    public static $CAN_NAPAM;
    public static $CHI_NAPAM;
    public static $NGUHANH_NAPAM;
    public static $CUUTINH_DAY_THEO_TIETKHI;
    //AGEGOODBAD
    public static $AGEGOOBAD;
    //BANG TIEUTHANH DAITHANH CANCHI
    public static $TIEUTHANH;
    public static $TIEUTHANHCANCHI;
    public static $DAITHANH;
    public static $DAITHANHCANCHI;
    //BANG TIEUTHANH DAITHANH CANCHI
    //NHITHAPBATTU
    public static $NHITHAPBATTU;
    public static $NHITHAPBATTULINHVAT;
    public static $NHITHAPBATTULINHVATHANNGU;
    public static $NHITHAPBATTUNGUHANH;
    public static $NHITHAPBATTUTOTXAU;
    public static $NGAYLUCNHAMS;
    public static $NHITHAPBATTUDAY;
    public static $DAYNAME;
    private $screen_base;
    private $settings_page;
    //NHITHAPBATTU
    //HOURKHONGVONG
    private static $TUANGIAP_LUCTHAP_HOAGIAP;
    private static $KHONGVONG_LUCTHAP_HOAGIAP;
    private static $KHONGVONG;

    public static $pxg_geofesh_data;

    /**
     * Private constructor to prevent multiple instantiations.
     */
    private function __construct()
    {
        self::$FIRST_DAY = $this->jdn(31, 1, 1200);
        self::$LAST_DAY = $this->jdn(31, 12, 2199);

        self::$TK13 = [
            0x226da2, 0x4695d0, 0x3349dc, 0x5849b0, 0x42a4b0, 0x2aaab8, 0x506a50, 0x3ab540, 0x24bb44, 0x48ab6,
            0x3495b0, 0x205372, 0x464970, 0x2e64f9, 0x5454b0, 0x3e6a50, 0x296c57, 0x4c5ac0, 0x36ab60, 0x2386e,
            0x4892e0, 0x30c97c, 0x56c960, 0x40d4a0, 0x2adaa8, 0x4eb550, 0x3a56a0, 0x24adb5, 0x4c25d0, 0x3492e,
            0x1ed2b2, 0x44a950, 0x2ed4d9, 0x52b2a0, 0x3cb550, 0x285757, 0x4e4da0, 0x36a5b0, 0x225574, 0x4852b,
            0x33a93c, 0x566930, 0x406aa0, 0x2aada8, 0x50ab50, 0x3a4b60, 0x24aae4, 0x4aa570, 0x365270, 0x1f526,
            0x42e530, 0x2e6cba, 0x5456a0, 0x3c5b50, 0x294ad6, 0x4e4ae0, 0x38a4e0, 0x20d4d4, 0x46d260, 0x30d53,
            0x56b520, 0x3eb6a0, 0x2b56a9, 0x505570, 0x3c49d0, 0x25a1b5, 0x4aa4b0, 0x34aa50, 0x1eea51, 0x42b52,
            0x2cb5aa, 0x52ab60, 0x3e95b0, 0x284b76, 0x4e4970, 0x3864b0, 0x22b4b3, 0x466a50, 0x306b3b, 0x565ac,
            0x40ab60, 0x2b2ad8, 0x5049e0, 0x3aa4d0, 0x24d4b5, 0x48b250, 0x32b520, 0x1cf522, 0x42b5a0, 0x2c95e,
            0x5295b0, 0x3e49b0, 0x28a576, 0x4ca4b0, 0x36aa50, 0x20ba54, 0x466d40, 0x2ead6c, 0x54ab60, 0x409370
        ];

        self::$TK14 = [
            0x2d49b8, 0x504970, 0x3a64b0, 0x246ca5, 0x48da50, 0x325aa0, 0x1cd6c1, 0x42a6e0, 0x2e92fb, 0x5292e,
            0x3cc960, 0x26d557, 0x4cd4a0, 0x34d550, 0x215553, 0x4656a0, 0x30a6d0, 0x1aa5d1, 0x4092b0, 0x2aa5b,
            0x50a950, 0x38b2a0, 0x23b2a5, 0x48ad50, 0x344da0, 0x1ccba1, 0x42a570, 0x2e52f9, 0x545270, 0x3c693,
            0x266b37, 0x4c6aa0, 0x36ab50, 0x205753, 0x464b60, 0x30a67c, 0x56a2e0, 0x3ed160, 0x28e968, 0x4ed4a,
            0x38daa0, 0x225ea5, 0x4856d0, 0x344ae0, 0x1f85d2, 0x42a2d0, 0x2cd17a, 0x52aa50, 0x3cb520, 0x24d74,
            0x4aada0, 0x3655d0, 0x2253b3, 0x4645b0, 0x30a2b0, 0x1ba2b1, 0x40aa50, 0x28b559, 0x4e6b20, 0x38ad6,
            0x255365, 0x489370, 0x344570, 0x1ea573, 0x4452b0, 0x2c6a6a, 0x50d950, 0x3c5aa0, 0x27aac7, 0x4aa6e,
            0x3652e0, 0x20cae3, 0x46a560, 0x2ed2bb, 0x54d2a0, 0x3ed550, 0x2a5ad9, 0x4e56a0, 0x38a6d0, 0x2455d,
            0x4a52b0, 0x32a8d0, 0x1ce552, 0x42b2a0, 0x2cb56a, 0x50ad50, 0x3c4da0, 0x26a7a6, 0x4ca570, 0x3651b,
            0x21a174, 0x466530, 0x316a9c, 0x545aa0, 0x3eab50, 0x2a2bd9, 0x502b60, 0x38a370, 0x2452e5, 0x48d160
        ];

        self::$TK15 = [
            0x32e4b0, 0x1c7523, 0x40daa0, 0x2d5b4b, 0x5256d0, 0x3c2ae0, 0x26a3d7, 0x4ca2d0, 0x36d150, 0x1ed95,
            0x44b520, 0x2eb69c, 0x54ada0, 0x3e55d0, 0x2b25b9, 0x5045b0, 0x3aa2b0, 0x22aab5, 0x48a950, 0x32b52,
            0x1ceaa1, 0x40ab60, 0x2c55bc, 0x524b70, 0x3e4570, 0x265377, 0x4c52b0, 0x366950, 0x216954, 0x445aa,
            0x2eab5c, 0x54a6e0, 0x404ae0, 0x28a5e8, 0x4ea560, 0x38d2a0, 0x22eaa6, 0x46d550, 0x3256a0, 0x1d95a,
            0x4295d0, 0x2c4afb, 0x5249b0, 0x3ca4d0, 0x26d2d7, 0x4ab2a0, 0x34b550, 0x205d54, 0x462da0, 0x2e95b,
            0x1b1571, 0x4049b0, 0x2aa4f9, 0x4e64b0, 0x386a90, 0x22aea6, 0x486b50, 0x322b60, 0x1caae2, 0x42937,
            0x2f496b, 0x50c960, 0x3ae4d0, 0x266b27, 0x4adaa0, 0x345ad0, 0x2036d3, 0x4626e0, 0x3092e0, 0x18d2d,
            0x3ec950, 0x28d4d9, 0x4eb4a0, 0x36b690, 0x2355a6, 0x4855b0, 0x3425d0, 0x1ca5b2, 0x4292b0, 0x2ca97,
            0x526950, 0x3a74a0, 0x24b5a8, 0x4aab60, 0x3653b0, 0x202b74, 0x462570, 0x3052b0, 0x1ad2b1, 0x3e695,
            0x286ad9, 0x4e5aa0, 0x38ab50, 0x224ed5, 0x484ae0, 0x32a370, 0x1f44e3, 0x40d2a0, 0x2bd94b, 0x50b550
        ];

        self::$TK16 = [
            0x3c56a0, 0x2497a7, 0x4a95d0, 0x364ae0, 0x20a9b4, 0x44a4d0, 0x2ed250, 0x19aaa1, 0x3eb550, 0x2856d,
            0x4e2da0, 0x3895b0, 0x244b75, 0x484970, 0x32a4b0, 0x1cb4b4, 0x426a90, 0x2aad5c, 0x505b50, 0x3c2b6,
            0x2695e8, 0x4a92f0, 0x364970, 0x206964, 0x44d4a0, 0x2cea5c, 0x52d690, 0x3e56d0, 0x2b2b5a, 0x4e26e,
            0x3892e0, 0x22cad6, 0x48c950, 0x30d4a0, 0x1af4a2, 0x40b590, 0x2c56dc, 0x5055b0, 0x3c25d0, 0x2693b,
            0x4c92b0, 0x34a950, 0x1fb155, 0x446ca0, 0x2ead50, 0x192b61, 0x3e4bb0, 0x2a25f9, 0x502570, 0x3852b,
            0x22aaa6, 0x46e950, 0x326aa0, 0x1abaa3, 0x40ab50, 0x2c4b7b, 0x524ae0, 0x3aa570, 0x2652d7, 0x4ad26,
            0x34d950, 0x1e5d55, 0x4456a0, 0x2e96d0, 0x1a55d2, 0x3e4ae0, 0x28a4fa, 0x4ea4d0, 0x38d250, 0x20d69,
            0x46b550, 0x3235a0, 0x1caba2, 0x4095b0, 0x2d49bc, 0x524970, 0x3ca4b0, 0x24b2b8, 0x4a6a50, 0x346d4,
            0x1fab54, 0x442ba0, 0x2e9370, 0x2e52f2, 0x544970, 0x3c64e9, 0x60d4a0, 0x4aea50, 0x373aa6, 0x5a56d,
            0x462b60, 0x3185e3, 0x5692e0, 0x3ec97b, 0x64a950, 0x4ed4a0, 0x38daa8, 0x5cb550, 0x4856b0, 0x342da4
        ];

        self::$TK17 = [
            0x58a5d0, 0x4292d0, 0x2cd2b2, 0x52a950, 0x3cb4d9, 0x606aa0, 0x4aad50, 0x365756, 0x5c4ba0, 0x44a5b,
            0x314573, 0x5652b0, 0x41a94b, 0x62e950, 0x4e6aa0, 0x38ada8, 0x5e9b50, 0x484b60, 0x32aae4, 0x58a4f,
            0x445260, 0x2bd262, 0x50d550, 0x3d5a9a, 0x6256a0, 0x4a96d0, 0x3749d6, 0x5c49e0, 0x46a4d0, 0x2ed4d,
            0x54d250, 0x3ed53b, 0x64b540, 0x4cb5a0, 0x3995a8, 0x5e95b0, 0x4a49b0, 0x32a974, 0x58a4b0, 0x42aa5,
            0x2cea51, 0x506d40, 0x3aadbb, 0x622b60, 0x4c9370, 0x364af6, 0x5c4970, 0x4664b0, 0x3074a3, 0x52da5,
            0x3e6b5b, 0x6456d0, 0x502ae0, 0x3893e7, 0x5e92e0, 0x48c960, 0x33d155, 0x56d4a0, 0x40da50, 0x2d355,
            0x5256a0, 0x3aa6fa, 0x6225d0, 0x4c92d0, 0x36aab6, 0x5aa950, 0x44b4a0, 0x2ebaa4, 0x54ad50, 0x3f55a,
            0x644ba0, 0x4ea5b0, 0x3b5278, 0x5e52b0, 0x486930, 0x327555, 0x586aa0, 0x40ab50, 0x2c5b52, 0x524b6,
            0x3da56a, 0x60a4f0, 0x4c5260, 0x34ea66, 0x5ad530, 0x445aa0, 0x2eb6a3, 0x5496d0, 0x404ae0, 0x28c9d,
            0x4ea4d0, 0x38d2d8, 0x5eb250, 0x46b520, 0x31d545, 0x56ada0, 0x4295d0, 0x2c55b2, 0x5249b0, 0x3ca4f9
        ];

        self::$TK18 = [
            0x62a4b0, 0x4caa50, 0x37b457, 0x5c6b40, 0x46ada0, 0x305b64, 0x569370, 0x424970, 0x2cc971, 0x5064b,
            0x3a6aa8, 0x5eda50, 0x4a5aa0, 0x32aec5, 0x58a6e0, 0x4492f0, 0x3052e2, 0x52c960, 0x3dd49a, 0x62d4a,
            0x4cd550, 0x365b57, 0x5c56a0, 0x46a6d0, 0x3295d4, 0x5692d0, 0x40a95c, 0x2ad4b0, 0x50b2a0, 0x38b5a,
            0x5ead50, 0x4a4da0, 0x34aba4, 0x58a570, 0x4452b0, 0x2eb273, 0x546930, 0x3c6abb, 0x626aa0, 0x4cab5,
            0x394b57, 0x5c4b60, 0x46a570, 0x3252e4, 0x56d160, 0x3ee93c, 0x64d520, 0x4edaa0, 0x3b5b29, 0x5e56d,
            0x4a4ae0, 0x34a5d5, 0x5aa2d0, 0x42d150, 0x2cea52, 0x52b520, 0x3cd6ab, 0x60ada0, 0x4c55d0, 0x384bb,
            0x5e45b0, 0x46a2b0, 0x30d2b4, 0x56aa50, 0x41b52c, 0x646b20, 0x4ead60, 0x3a55e9, 0x609370, 0x4a457,
            0x34a575, 0x5a52b0, 0x446a50, 0x2d5a52, 0x525aa0, 0x3dab4b, 0x62a6e0, 0x4c92e0, 0x36c6e6, 0x5ca56,
            0x46d4a0, 0x2eeaa5, 0x54d550, 0x4056a0, 0x2ad5a1, 0x4ea5d0, 0x3b52d9, 0x6052b0, 0x4aa950, 0x32d55,
            0x58b2a0, 0x42b550, 0x2e6d52, 0x524da0, 0x3da5cb, 0x62a570, 0x4e51b0, 0x36a977, 0x5c6530, 0x466a90
        ];

        self::$TK19 = [
            0x30baa3, 0x56ab50, 0x422ba0, 0x2cab61, 0x52a370, 0x3c51e8, 0x60d160, 0x4ae4b0, 0x376926, 0x58daa0,
            0x445b50, 0x3116d2, 0x562ae0, 0x3ea2e0, 0x28e2d2, 0x4ec950, 0x38d556, 0x5cb520, 0x46b690, 0x325da4,
            0x5855d0, 0x4225d0, 0x2ca5b3, 0x52a2b0, 0x3da8b7, 0x60a950, 0x4ab4a0, 0x35b2a5, 0x5aad50, 0x4455b0,
            0x302b74, 0x562570, 0x4052f9, 0x6452b0, 0x4e6950, 0x386d56, 0x5e5aa0, 0x46ab50, 0x3256d4, 0x584ae0,
            0x42a570, 0x2d4553, 0x50d2a0, 0x3be8a7, 0x60d550, 0x4a5aa0, 0x34ada5, 0x5a95d0, 0x464ae0, 0x2eaab4,
            0x54a4d0, 0x3ed2b8, 0x64b290, 0x4cb550, 0x385757, 0x5e2da0, 0x4895d0, 0x324d75, 0x5849b0, 0x42a4b0,
            0x2da4b3, 0x506a90, 0x3aad98, 0x606b50, 0x4c2b60, 0x359365, 0x5a9370, 0x464970, 0x306964, 0x52e4a0,
            0x3cea6a, 0x62da90, 0x4e5ad0, 0x392ad6, 0x5e2ae0, 0x4892e0, 0x32cad5, 0x56c950, 0x40d4a0, 0x2bd4a3,
            0x50b690, 0x3a57a7, 0x6055b0, 0x4c25d0, 0x3695b5, 0x5a92b0, 0x44a950, 0x2ed954, 0x54b4a0, 0x3cb550,
            0x286b52, 0x4e55b0, 0x3a2776, 0x5e2570, 0x4852b0, 0x32aaa5, 0x56e950, 0x406aa0, 0x2abaa3, 0x50ab50
        ]; /* Years 1800-1899 */

        self::$TK20 = [
            0x3c4bd8, 0x624ae0, 0x4ca570, 0x3854d5, 0x5cd260, 0x44d950, 0x315554, 0x5656a0, 0x409ad0, 0x2a55d2,
            0x504ae0, 0x3aa5b6, 0x60a4d0, 0x48d250, 0x33d255, 0x58b540, 0x42d6a0, 0x2cada2, 0x5295b0, 0x3f4977,
            0x644970, 0x4ca4b0, 0x36b4b5, 0x5c6a50, 0x466d50, 0x312b54, 0x562b60, 0x409570, 0x2c52f2, 0x504970,
            0x3a6566, 0x5ed4a0, 0x48ea50, 0x336a95, 0x585ad0, 0x442b60, 0x2f86e3, 0x5292e0, 0x3dc8d7, 0x62c950,
            0x4cd4a0, 0x35d8a6, 0x5ab550, 0x4656a0, 0x31a5b4, 0x5625d0, 0x4092d0, 0x2ad2b2, 0x50a950, 0x38b557,
            0x5e6ca0, 0x48b550, 0x355355, 0x584da0, 0x42a5b0, 0x2f4573, 0x5452b0, 0x3ca9a8, 0x60e950, 0x4c6aa0,
            0x36aea6, 0x5aab50, 0x464b60, 0x30aae4, 0x56a570, 0x405260, 0x28f263, 0x4ed940, 0x38db47, 0x5cd6a0,
            0x4896d0, 0x344dd5, 0x5a4ad0, 0x42a4d0, 0x2cd4b4, 0x52b250, 0x3cd558, 0x60b540, 0x4ab5a0, 0x3755a6,
            0x5c95b0, 0x4649b0, 0x30a974, 0x56a4b0, 0x40aa50, 0x29aa52, 0x4e6d20, 0x39ad47, 0x5eab60, 0x489370,
            0x344af5, 0x5a4970, 0x4464b0, 0x2c74a3, 0x50ea50, 0x3d6a58, 0x6256a0, 0x4aaad0, 0x3696d5, 0x5c92e0
        ]; /* Years 1900-1999 */

        self::$TK21 = [
            0x46c960, 0x2ed954, 0x54d4a0, 0x3eda50, 0x2a7552, 0x4e56a0, 0x38a7a7, 0x5ea5d0, 0x4a92b0, 0x32aab5,
            0x58a950, 0x42b4a0, 0x2cbaa4, 0x50ad50, 0x3c55d9, 0x624ba0, 0x4ca5b0, 0x375176, 0x5c5270, 0x466930,
            0x307934, 0x546aa0, 0x3ead50, 0x2a5b52, 0x504b60, 0x38a6e6, 0x5ea4e0, 0x48d260, 0x32ea65, 0x56d520,
            0x40daa0, 0x2d56a3, 0x5256d0, 0x3c4afb, 0x6249d0, 0x4ca4d0, 0x37d0b6, 0x5ab250, 0x44b520, 0x2edd25,
            0x54b5a0, 0x3e55d0, 0x2a55b2, 0x5049b0, 0x3aa577, 0x5ea4b0, 0x48aa50, 0x33b255, 0x586d20, 0x40ad60,
            0x2d4b63, 0x525370, 0x3e49e8, 0x60c970, 0x4c54b0, 0x3768a6, 0x5ada50, 0x445aa0, 0x2fa6a4, 0x54aad0,
            0x4052e0, 0x28d2e3, 0x4ec950, 0x38d557, 0x5ed4a0, 0x46d950, 0x325d55, 0x5856a0, 0x42a6d0, 0x2c55d4,
            0x5252b0, 0x3ca9b8, 0x62a930, 0x4ab490, 0x34b6a6, 0x5aad50, 0x4655a0, 0x2eab64, 0x54a570, 0x4052b0,
            0x2ab173, 0x4e6930, 0x386b37, 0x5e6aa0, 0x48ad50, 0x332ad5, 0x582b60, 0x42a570, 0x2e52e4, 0x50d160,
            0x3ae958, 0x60d520, 0x4ada90, 0x355aa6, 0x5a56d0, 0x462ae0, 0x30a9d4, 0x54a2d0, 0x3ed150, 0x28e952
        ]; /* Years 2000-2099 */

        self::$TK22 = [
            0x4eb520, 0x38d727, 0x5eada0, 0x4a55b0, 0x362db5, 0x5a45b0, 0x44a2b0, 0x2eb2b4, 0x54a950, 0x3cb559,
            0x626b20, 0x4cad50, 0x385766, 0x5c5370, 0x484570, 0x326574, 0x5852b0, 0x406950, 0x2a7953, 0x505aa0,
            0x3baaa7, 0x5ea6d0, 0x4a4ae0, 0x35a2e5, 0x5aa550, 0x42d2a0, 0x2de2a4, 0x52d550, 0x3e5abb, 0x6256a0,
            0x4c96d0, 0x3949b6, 0x5e4ab0, 0x46a8d0, 0x30d4b5, 0x56b290, 0x40b550, 0x2a6d52, 0x504da0, 0x3b9567,
            0x609570, 0x4a49b0, 0x34a975, 0x5a64b0, 0x446a90, 0x2cba94, 0x526b50, 0x3e2b60, 0x28ab61, 0x4c9570,
            0x384ae6, 0x5cd160, 0x46e4a0, 0x2eed25, 0x54da90, 0x405b50, 0x2c36d3, 0x502ae0, 0x3a93d7, 0x6092d0,
            0x4ac950, 0x32d556, 0x58b4a0, 0x42b690, 0x2e5d94, 0x5255b0, 0x3e25fa, 0x6425b0, 0x4e92b0, 0x36aab6,
            0x5c6950, 0x4674a0, 0x31b2a5, 0x54ad50, 0x4055a0, 0x2aab73, 0x522570, 0x3a5377, 0x6052b0, 0x4a6950,
            0x346d56, 0x585aa0, 0x42ab50, 0x2e56d4, 0x544ae0, 0x3ca570, 0x2864d2, 0x4cd260, 0x36eaa6, 0x5ad550,
            0x465aa0, 0x30ada5, 0x5695d0, 0x404ad0, 0x2aa9b3, 0x50a4d0, 0x3ad2b7, 0x5eb250, 0x48b540, 0x33d556
        ];

        /* Years 2100-2199 */
        self::$CUUTINH_DAY_THEO_TIETKHI = [[1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6], [7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3], [4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9], [9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4], [3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7], [6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1]];

        self::$DAYS_EN = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        self::$DAYS_VI = ['Chủ Nhật', 'Thứ 2', 'Thứ 3', 'Thứ 4', 'Thứ 5', 'Thứ 6', 'Thứ 7'];
        self::$MONTH_EN = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
        self::$MONTH_VI = ['Tháng 1', 'Tháng 2', 'Tháng 3', 'Tháng 4', 'Tháng 5', 'Tháng 6', 'Tháng 7', 'Tháng 8', 'Tháng 9', 'Tháng 10', 'Tháng 11', 'Tháng 12'];

        self::$TUAN = ["Chủ Nhật", "Thứ Hai", "Thứ Ba", "Thứ Tư", "Thứ Năm", "Thứ Sáu", "Thứ Bảy"];
        self::$THANG = ["Giêng", "Hai", "Ba", "Tư", "Năm", "Sáu", "Bảy", "Tám", "Chín", "Mười", "Một", "Chạp"];
        self::$CAN = ["Giáp", "Ất", "Bính", "Đinh", "Mậu", "Kỷ", "Canh", "Tân", "Nhâm", "Quý"];
        self::$CANNGUHANH = ["+Mộc", "-Mộc", "+Hỏa", "-Hỏa", "+Thổ", "-Thổ", "+Kim", "-Kim", "+Thủy", "-Thủy"];
        self::$CHI = ["Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi"];
        self::$TANGCAN_DIACHI = [["Quý"], ["Kỷ", "Quý", "Tân"], ["Giáp", "Bính", "Mậu"], ["Ất"], ["Mậu", "Ất", "Quý"], ["Bính", "Mậu", "Canh"], ["Đinh", "Kỷ"], ["Kỷ", "Đinh", "Ất"], ["Canh", "Nhâm", "Mậu"], ["Tân"], ["Mậu", "Tân", "Đinh"], ["Nhâm", "Giáp"]];
        self::$CHINGUHANH = ["+Thủy", "-Thổ", "+Mộc", "-Mộc", "+Thổ", "-Hỏa", "+Hỏa", "-Thổ", "+Kim", "-Kim", "+Thổ", "-Thủy"];
        self::$GIO_HD = ["110100101100", "001101001011", "110011010010", "101100110100", "001011001101", "010010110011"];
        self::$TIETKHI = ["Xuân Phân", "Thanh Minh", "Cốc Vũ", "Lập Hạ", "Tiểu Mãn", "Mang Chủng", "Hạ Chí", "Tiểu Thử", "Đại Thử", "Lập Thu", "Xử Thử", "Bạch Lộ", "Thu Phân", "Hàn Lộ", "Sương Giáng", "Lập Đông", "Tiểu Tuyết", "Đại Tuyết", "Đông Chí", "Tiểu Hàn", "Đại Hàn", "Lập Xuân", "Vũ Thủy", "Kinh Trập"];
        self::$TIET_KHI_DEGREE = [0, 15, 30, 45, 60, 75, 90, 105, 120, 135, 150, 165, 180, 195, 210, 225, 240, 255, 270, 285, 300, 315, 330, 345];
        self::$TIET12 = ["Tiểu Hàn", "Lập Xuân", "Kinh Trập", "Thanh Minh", "Lập Hạ", "Mang Chủng", "Tiểu Thử", "Lập Thu", "Bạch Lộ", "Hàn Lộ", "Lập Đông", "Đại Tuyết"];/*12 tháng nông lịch*/
        self::$TIET12KHI12 = [["Tiểu Hàn", "Đại Hàn"], ["Lập Xuân", "Vũ Thủy"], ["Kinh Trập", "Xuân Phân"], ["Thanh Minh", "Cốc Vũ"], ["Lập Hạ", "Tiểu Mãn"], ["Mang Chủng", "Hạ Chí"], ["Tiểu Thử", "Đại Thử"], ["Lập Thu", "Xử Thử"], ["Bạch Lộ", "Thu Phân"], ["Hàn Lộ", "Sương Giáng"], ["Lập Đông", "Tiểu Tuyết"], ["Đại Tuyết", "Đông Chí"]];

        //TODO: tính Cung Phi theo Hướng.
        self::$arrCompassCung = ["Bắc", "Tây Nam", "Đông", "Đông Nam", "5:2/8", "Tây Bắc", "Tây", "Đông Bắc", "Nam"];
        self::$QUEBATQUAI = ["Khảm", "Khôn", "Chấn", "Tốn", "5:2/8", "Càn", "Đoài", "Cấn", "Ly"];
        self::$arrNguHanhCung = ["Thủy", "Thổ", "Mộc", "Mộc", "Thổ", "Kim", "Kim", "Thổ", "Hỏa"];

        //CÁC HƯỚNG
        self::$SinhKhi = ["Đông Nam", "Đông Bắc", "Nam", "Bắc", "5:2/8", "Tây", "Tây Bắc", "Tây Nam", "Đông"];
        self::$ThienY = ["Đông", "Tây", "Bắc", "Nam", "5:2/8", "Đông Bắc", "Tây Nam", "Tây Bắc", "Đông Nam"];
        self::$DienNien = ["Nam", "Tây Bắc", "Đông Nam", "Đông", "5:2/8", "Tây Nam", "Đông Bắc", "Tây", "Bắc"];
        self::$PhucVi = ["Bắc", "Tây Nam", "Đông", "Đông Nam", "5:2/8", "Tây Bắc", "Tây", "Đông Bắc", "Nam"];

        self::$HoaHai = ["Tây", "Đông", "Tây Nam", "Tây Bắc", "5:2/8", "Đông Nam", "Bắc", "Nam", "Đông Bắc"];
        self::$NguQuy = ["Đông Bắc", "Đông Nam", "Tây Bắc", "Tây Nam", "5:2/8", "Đông", "Nam", "Bắc", "Tây"];
        self::$LucSat = ["Tây Bắc", "Nam", "Đông Bắc", "Tây", "5:2/8", "Bắc", "Đông Nam", "Đông", "Tây Nam"];
        self::$TuyetMenh = ["Tây Nam", "Bắc", "Tây", "Đông Bắc", "5:2/8", "Nam", "Đông", "Đông Nam", "Tây Bắc"];

        //THẦN SÁT
        //Solar = Dương Lịch. Lunar = Âm Lịch
        self::$QuyNhan = ["Tỵ", "Ngọ", "Thân", "Dậu", "Thân", "Dậu", "Hợi", "Tý", "Dần", "Mão"];
        self::$VanXuong = ["Tỵ", "Ngọ", "Thân", "Dậu", "Thân", "Dậu", "Hợi", "Tý", "Dần", "Mão"];
        self::$HocDuong = ["Hợi", "Tý", "Dần", "Dậu", "Dần", "Dậu", "Tỵ", "Tý", "Thân", "Mão"];

        self::$ThienHyYear = ["Dậu", "Thân", "Mùi", "Ngọ", "Tỵ", "Thìn", "Mão", "Dần", "Sửu", "Tý", "Hợi", "Tuất"];
        self::$ThienHyMonth = ["Mùi", "Mùi", "Tuất", "Tuất", "Tuất", "Sửu", "Sửu", "Sửu", "Thìn", "Thìn", "Thìn", "Mùi"];
        self::$KiepSat = ["Tỵ", "Dần", "Hợi", "Thân", "Tỵ", "Dần", "Hợi", "Thân", "Tỵ", "Dần", "Hợi", "Thân"];
        self::$HongLoan = ["Mão", "Dần", "Sửu", "Tý", "Hợi", "Tuất", "Dậu", "Thân", "Mùi", "Ngọ", "Tỵ", "Thìn"];

        //THẬP THẦN
        self::$THAPTHAN = ["Tỷ Kiên", "Kiếp Tài", "Thực Thần", "Thương Quan", "Thiên Tài", "Chính Tài", "Thất Sát", "Chính Quan", "Thiên Ấn", "Chính Ấn"];
        self::$THAPTHANKEY = ["TK", "KT", "TH", "TQ", "TT", "CT", "TS", "CQ", "TA", "CA"];
        self::$THAPTHAN_GIAP = ["Giáp", "Ất", "Bính", "Đinh", "Mậu", "Kỷ", "Canh", "Tân", "Nhâm", "Quý"];
        self::$THAPTHAN_AT = ["Ất", "Giáp", "Đinh", "Bính", "Kỷ", "Mậu", "Tân", "Canh", "Quý", "Nhâm"];
        self::$THAPTHAN_BINH = ["Bính", "Đinh", "Mậu", "Kỷ", "Canh", "Tân", "Nhâm", "Quý", "Giáp", "Ất"];
        self::$THAPTHAN_DINH = ["Đinh", "Bính", "Kỷ", "Mậu", "Tân", "Canh", "Quý", "Nhâm", "Ất", "Giáp"];
        self::$THAPTHAN_MAU = ["Mậu", "Kỷ", "Canh", "Tân", "Nhâm", "Quý", "Giáp", "Ất", "Bính", "Đinh"];
        self::$THAPTHAN_KY = ["Kỷ", "Mậu", "Tân", "Canh", "Quý", "Nhâm", "Ất", "Giáp", "Đinh", "Bính"];
        self::$THAPTHAN_CANH = ["Canh", "Tân", "Nhâm", "Quý", "Giáp", "Ất", "Bính", "Đinh", "Mậu", "Kỷ"];
        self::$THAPTHAN_TAN = ["Tân", "Canh", "Quý", "Nhâm", "Ất", "Giáp", "Đinh", "Bính", "Kỷ", "Mậu"];
        self::$THAPTHAN_NHAM = ["Nhâm", "Quý", "Giáp", "Ất", "Bính", "Đinh", "Mậu", "Kỷ", "Canh", "Tân"];
        self::$THAPTHAN_QUY = ["Quý", "Nhâm", "Ất", "Giáp", "Đinh", "Bính", "Kỷ", "Mậu", "Tân", "Canh"];

        //TODO: Thập Nhị Trực
        self::$THAPNHITRUC = ["Kiến", "Trừ", "Mãn", "Bình", "Định", "Chấp", "Phá", "Nguy", "Thành", "Thâu", "Khai", "Bế"];
        self::$THAPNHITRUCTHANG = [["Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý"]/*T1*/, ["Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu"]/*T2*/, ["Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần"]/*T3*/, ["Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão"]/*T4*/, ["Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn"]/*T5*/, ["Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ"]/*T6*/, ["Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ"]/*T7*/, ["Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi"]/*T8*/, ["Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân"]/*T9*/, ["Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu"]/*T10*/, ["Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất"]/*T11*/, ["Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi"]/*T12*/];
        //TODO: Thập Nhị Trực

        //TODO: Kiết Hung Nhật
        self::$KIETHUNGNHAT = ["Thanh Long", "Minh Đường", "Thiên Hình", "Chu Tước", "Kim Quỹ", "Thiên Đức", "Bạch Hổ", "Ngọc Đường", "Thiên Lao", "Vũ Huyền", "Tư Mệnh", "Câu Trần"];
        self::$KIETHUNGNHATTOTXAU = ["Thanh Long Hoàng Đạo", "Minh Đường Hoàng Đạo", "Thiên Hình Hắc Đạo", "Chu Tước Hắc Đạo", "Kim Quỹ Hoàng Đạo", "Thiên Đức Hoàng Đạo", "Bạch Hổ Hắc Đạo", "Ngọc Đường Hoàng Đạo", "Thiên Lao Hắc Đạo", "Vũ Huyền Hắc Đạo", "Tư Mệnh Hoàng Đạo", "Câu Trần Hắc Đạo"];
        self::$NGAYHOANGDAO = ["Thanh Long", "Minh Đường", "Kim Quỹ", "Thiên Đức", "Ngọc Đường", "Tư Mệnh"];
        self::$NGAYHACDAO = ["Thiên Hình", "Chu Tước", "Bạch Hổ", "Thiên Lao", "Huyền Vũ", "Câu Trần"];

        self::$KIETHUNGNGAY = [["Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi"]/*Tý*/, ["Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu"]/*Sửu*/, ["Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi"], ["Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu"], ["Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão"], ["Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ"]/*Tỵ*/, ["Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi"], ["Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu"], ["Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi"]/*Thân*/, ["Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu"]/*Dậu*/, ["Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão"]/*Tuất*/, ["Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ"]/*Hợi*/];;
        //TODO: Kiết Hung Nhật

        //CUUCUNGPHITINH
        self::$CUUCUNGPHITINH = ["Nhất Bạch", "Nhị Hắc", "Tam Bích", "Tứ Lục", "Ngũ Hoàng Đại Sát", "Lục Bạch", "Thất Xích", "Bát Bạch", "Cửu Tử"];/*1-9*/
        self::$LUONGTHIENXICH = ["TÂY BẮC", "TÂY", "ĐÔNG BẮC", "NAM", "BẮC", "TÂY NAM", "ĐÔNG", "ĐÔNG NAM"];
        self::$LUONGTHIENXICHTABLE = ["ĐÔNG NAM", "NAM", "TÂY NAM", "ĐÔNG", "TÂY", "ĐÔNG BẮC", "BẮC", "TÂY BẮC"];

        //TODO: CUUTINHNGAY
        self::$TIETKHI_CUUTINH = [["Đông Chí", "Tiểu Hàn", "Đại Hàn", "Lập Xuân"], ["Vũ Thủy", "Kinh Trập", "Xuân Phân", "Thanh Minh"], ["Cốc Vũ", "Lập Hạ", "Tiểu Mãn", "Mang Chủng"], ["Hạ Chí", "Tiểu Thử", "Đại Thử", "Lập Thu"], ["Xử Thử", "Bạch Lộ", "Thu Phân", "Hàn Lộ"], ["Sương Giáng", "Lập Đông", "Tiểu Tuyết", "Đại Tuyết"]];
        self::$CUUTINH_NGAY_THEO_TIETKHI = [1, 7, 4, 9, 3, 6];
        self::$DAYCANCHI_LUCTHAP_HOAGIAP = ["Giáp Tý", "Ất Sửu", "Bính Dần", "Đinh Mão", "Mậu Thìn", "Kỷ Tỵ", "Canh Ngọ", "Tân Mùi", "Nhâm Thân", "Quý Dậu", "Giáp Tuất", "Ất Hợi", "Bính Tý", "Đinh Sửu", "Mậu Dần", "Kỷ Mão", "Canh Thìn", "Tân Tỵ", "Nhâm Ngọ", "Quý Mùi", "Giáp Thân", "Ất Dậu", "Bính Tuất", "Đinh Hợi", "Mậu Tý", "Kỷ Sửu", "Canh Dần", "Tân Mão", "Nhâm Thìn", "Quý Tỵ", "Giáp Ngọ", "Ất Mùi", "Bính Thân", "Đinh Dậu", "Mậu Tuất", "Kỷ Hợi", "Canh Tý", "Tân Sửu", "Nhâm Dần", "Quý Mão", "Giáp Thìn", "Ất Tỵ", "Bính Ngọ", "Đinh Mùi", "Mậu Thân", "Kỷ Dậu", "Canh Tuất", "Tân Hợi", "Nhâm Tý", "Quý Sửu", "Giáp Dần", "Ất Mão", "Bính Thìn", "Đinh Tỵ", "Mậu Ngọ", "Kỷ Mùi", "Canh Thân", "Tân Dậu", "Nhâm Tuất", "Quý Hợi"];
        self::$TUANGIAP_LUCTHAP_HOAGIAP = ['Giáp Tý', 'Giáp Tuất', 'Giáp Thân', 'Giáp Ngọ', 'Giáp Thìn', 'Giáp Dần'];
        self::$KHONGVONG_LUCTHAP_HOAGIAP = [["Giáp Tý", "Ất Sửu", "Bính Dần", "Đinh Mão", "Mậu Thìn", "Kỷ Tỵ", "Canh Ngọ", "Tân Mùi", "Nhâm Thân", "Quý Dậu"], ["Giáp Tuất", "Ất Hợi", "Bính Tý", "Đinh Sửu", "Mậu Dần", "Kỷ Mão", "Canh Thìn", "Tân Tỵ", "Nhâm Ngọ", "Quý Mùi"], ["Giáp Thân", "Ất Dậu", "Bính Tuất", "Đinh Hợi", "Mậu Tý", "Kỷ Sửu", "Canh Dần", "Tân Mão", "Nhâm Thìn", "Quý Tỵ"], ["Giáp Ngọ", "Ất Mùi", "Bính Thân", "Đinh Dậu", "Mậu Tuất", "Kỷ Hợi", "Canh Tý", "Tân Sửu", "Nhâm Dần", "Quý Mão"], ["Giáp Thìn", "Ất Tỵ", "Bính Ngọ", "Đinh Mùi", "Mậu Thân", "Kỷ Dậu", "Canh Tuất", "Tân Hợi", "Nhâm Tý", "Quý Sửu"], ["Giáp Dần", "Ất Mão", "Bính Thìn", "Đinh Tỵ", "Mậu Ngọ", "Kỷ Mùi", "Canh Thân", "Tân Dậu", "Nhâm Tuất", "Quý Hợi"]];
        self::$KHONGVONG = ["Tuất Hợi", "Thân Dậu", "Ngọ Mùi", "Thìn Tỵ", "Dần Mão", "Tý Sửu"];
        //TODO: CUUTINHNGAY

        //CUUTINHHOURS
        self::$CUUTINH_HOUR_CHI_DAY = ["Tý Ngọ Mão Dậu", "Dần Thân Tỵ Hợi", "Thìn Tuất Sửu Mùi"];
        self::$GIOCUUTINH_CHI = [[[1, 2, 3, 4, 5, 6, 7, 8, 9, 1, 2, 3], [9, 8, 7, 6, 5, 4, 3, 2, 1, 9, 8, 7]], [[7, 8, 9, 1, 2, 3, 4, 5, 6, 7, 8, 9], [3, 2, 1, 9, 8, 7, 6, 5, 4, 3, 2, 1]], [[4, 5, 6, 7, 8, 9, 1, 2, 3, 4, 5, 6], [6, 5, 4, 3, 2, 1, 9, 8, 7, 6, 5, 4]]];
        self::$TIETKHI_CUUTINH_HOUR = [["Đông Chí", "Tiểu Hàn", "Đại Hàn", "Lập Xuân", "Vũ Thủy", "Kinh Trập", "Xuân Phân", "Thanh Minh", "Cốc Vũ", "Lập Hạ", "Tiểu Mãn", "Mang Chủng"], ["Hạ Chí", "Tiểu Thử", "Đại Thử", "Lập Thu", "Xử Thử", "Bạch Lộ", "Thu Phân", "Hàn Lộ", "Sương Giáng", "Lập Đông", "Tiểu Tuyết", "Đại Tuyết"]];
        //CUUTINHHOURS

        //CUUTINHMONTH
        self::$CUUTINHMONTH = [[8, 5, 2], [7, 4, 1], [6, 3, 9], [5, 2, 8], [4, 1, 7], [3, 9, 6], [2, 8, 5], [1, 7, 4], [9, 6, 3], [8, 5, 2], [7, 4, 1], [6, 3, 9]];
        self::$CUUTINHMONTHYEAR = ["Tý Ngọ Mão Dậu", "Thìn Tuất Sửu Mùi", "Dần Thân Tỵ Hợi"];
        //CUUTINHMONTH

        //TAMSAT-NIEN-NGUYET-NHAT-THOI
        self::$TAMSATCHI = [["Dần", "Ngọ", "Tuất"], ["Thân", "Tý", "Thìn"], ["Hợi", "Mão", "Mùi"], ["Tỵ", "Dậu", "Sửu"]];
        self::$TAMSAT = [["Đông Bắc", "Bắc", "Tây Bắc"], ["Đông Nam", "Nam", "Tây Nam"], ["Tây Nam", "Tây", "Tây Bắc"], ["Đông Bắc", "Đông", "Đông Nam"]];
        //TAMSAT-NIEN-NGUYET-NHAT-THOI

        //NAPAM CAN-CHI
        self::$CAN_NAPAM = ["Giáp Ất", "Bính Đinh", "Mậu Kỷ", "Canh Tân", "Nhâm Quý"];
        self::$CHI_NAPAM = ["Tý Sửu Ngọ Mùi", "Dần Mão Thân Dậu", "Thìn Tị Tuất Hợi"];
        self::$NGUHANH_NAPAM = ["Kim", "Thủy", "Hỏa", "Thổ", "Mộc"];
        //NAPAM CAN-CHI

        //AGE GoodBad by CAN CHI
        self::$AGEGOOBAD = [["Mậu Ngọ", "Nhâm Ngọ", "Canh Dần", "Canh Thân"], ["Kỷ Mùi", "Quý Mùi", "Tân Mão", "Tân Dậu"], ["Giáp Thân", "Nhâm Thân", "Nhâm Tuất", "Nhâm Thìn"], ["Ất Dậu", "Quý Dậu", "Quý Tỵ", "Quý Hợi"], ["Canh Tuất", "Bính Tuất"], ["Tân Hợi", "Đinh Hợi"], ["Nhâm Tý", "Bính Tý", "Giáp Thân", "Giáp Dần"], ["Quý Sửu", "Đinh Sửu", "Ất Dậu", "Ất Mão"], ["Bính Dần", "Canh Dần", "Bính Thân"], ["Đinh Mão", "Tân Mão", "Đinh Dậu"], ["Nhâm Thìn", "Canh Thìn", "Canh Tuất"], ["Quý Tỵ", "Tân Tỵ", "Tân Hợi"], ["Canh Ngo", "Mậu Ngọ"], ["Tân Mùi", "Kỷ Mùi"], ["Canh Thân", "Giáp Thân"], ["Tân Dậu", "Ất Dậu"], ["Giáp Tuất", "Mậu Tuất", "Giáp Thìn"], ["Ất Hợi", "Kỷ Hợi", "Ất Tỵ"], ["Giáp Tý", "Canh Tý", "Bính Tuất", "Bính Thìn"], ["Ất Sửu", "Tân Sửu", "Đinh Hợi", "Đinh Tỵ"], ["Mậu Dần", "Bính Dần", "Canh Ngọ", "Canh Tý"], ["Kỷ Mão", "Đinh Mão", "Tân Mùi", "Tân Sửu"], ["Mậu Thìn", "Nhâm Thìn", "Nhâm Ngọ", "Nhâm Tý"], ["Kỷ Tỵ", "Quý Tỵ", "Quý Mùi", "Quý Sửu"], ["Bính Ngọ", "Giáp Ngọ"], ["Đinh Mùi", "Ất Mùi"], ["Nhâm Thân", "Mậu Thân", "Giáp Tý", "Giáp Ngọ"], ["Quý Dậu", "Kỷ Dậu", "Ất Sửu", "Ất Mùi"], ["Bính Tuất", "Giáp Tuât", "Bính Dần"], ["Đinh Hợi", "Ất Hợi", "Đinh Mão"], ["Mậu Tý", "Nhâm Tý", "Canh Dần", "Nhâm Dần"], ["Kỷ Sửu", "Quý Sửu", "Tân Mão", "Tân Dậu"], ["Giáp Dần", "Nhâm Thân", "Nhâm Tuất", "Nhâm Thìn"], ["Ất Mão", "Quý Mão", "Quý Tỵ", "Quý Hợi"], ["Canh Thìn", "Bính Thìn"], ["Tân Tỵ", "Đinh Tỵ"], ["Nhâm Ngọ", "Bính Ngọ", "Giáp Thân", "Giáp Dần"], ["Quý Mùi", "Đinh Mùi", "Ất Dậu", "Ất Mão"], ["Canh Thân", "Bính Thân", "Bính Dần"], ["Tân Dậu", "Đinh Dậu", "Đinh Mão"], ["Nhâm Tuất", "Canh Tuất", "Canh Thìn"], ["Quý Hợi", "Tân Hợi", "Tân Tỵ"], ["Mậu Tý", "Canh Tý"], ["Kỷ Sửu", "Tân Sửu"], ["Canh Dần", "Giáp Dần"], ["Tân Mão", "Ất Mão"], ["Giáp Thìn", "Mậu Thìn", "Giáp Tuất"], ["Ất Tỵ", "Kỷ Tỵ", "Ất Hợi"], ["Giáp Ngọ", "Canh Ngọ", "Bính Tuất", "Bính Thìn"], ["Ất Mùi", "Tân Mùi", "Đinh Hợi", "Đinh Tỵ"], ["Mậu Thân", "Bính Thân", "Canh Ngọ", "Canh Tý"], ["Kỷ Dậu", "Đinh Dậu", "Tân Mùi", "Tân Sửu"], ["Mậu Tuất", "Nhâm Tuất", "Nhâm Ngọ", "Nhâm Tý"], ["Kỷ Hợi", "Quý Hợi", "Quý Sửu", "Quý Mùi"], ["Bính Tý", "Giáp Tý"], ["Đinh Sửu", "Ất Sửu"], ["Nhâm Dần", "Mậu Dần", "Giáp Tý", "Giáp Ngọ"], ["Quý Mão", "Kỷ Mão", "Ất Sửu", "Ất Mùi"], ["Bính Thìn", "Giáp Thìn", "Bính Thân", "Bính Dần"], ["Đinh Tỵ", "Ất Tỵ", "Đinh Mão", "Đinh Dậu"]];
        //AGE GoodBad by CAN CHI

        //BANG TIEUTHANH DAITHANH CANCHI
        self::$TIEUTHANH = ["Hải Trung", "Kiếm Phong", "Bạch Lạp", "Tích Lịch", "Sơn Hạ", "Phú Đăng", "Tăng Đố", "Thạch Lựu", "Đại Lâm", "Giang Hạ", "Tuyền Trung", "Trường Lưu", "Bích Thượng", "Đại Dịch", "Sa Trung"];
        self::$TIEUTHANHCANCHI = [["Giáp Tý", "Ất Sửu"], ["Nhâm Thân", "Quý Dậu"], ["Canh Thìn", "Tân Tỵ"], ["Mậu Tý", "Kỷ Sửu"], ["Bính Thân", "Đinh Dậu"], ["Giáp Thìn", "Ất Tỵ"], ["Nhâm Tý", "Quý Sửu"], ["Canh Thân", "Tân Dậu"], ["Mậu Thìn", "Kỷ Tỵ"], ["Bính Tý", "Đinh Sửu"], ["Giáp Thân", "Ất Dậu"], ["Nhâm Thìn", "Quý Tỵ"], ["Canh Tý", "Tân Sửu"], ["Mậu Thân", "Kỷ Dậu"], ["Bính Thìn", "Đinh Tỵ"]];
        self::$DAITHANH = ["Sa Trung", "Kim Bạch", "Thoa Xuyến", "Thiên Thượng", "Lư Trung", "Sơn Đầu", "Dương Liễu", "Tùng Bá", "Bình Địa", "Thiên Hà", "Ðại Khê", "Ðại Hải", "Lộ Bàng", "Thành Đầu", "Ốc Thượng"];
        self::$DAITHANHCANCHI = [["Giáp Ngọ", "Ất Mùi"], ["Nhâm Dần", "Quý Mão"], ["Canh Tuất", "Tân Hợi"], ["Mậu Ngọ", "Kỷ Mùi"], ["Bính Dần", "Đinh Mão"], ["Giáp Tuất", "Ất Hợi"], ["Nhâm Ngọ", "Quý Mùi"], ["Canh Dần", "Tân Mão"], ["Mậu Tuất", "Kỷ Hợi"], ["Bính Ngọ", "Đinh Mùi"], ["Giáp Dần", "Ất Mão"], ["Nhâm Tuất", "Quý Hợi"], ["Canh Ngọ", "Tân Mùi"], ["Mậu Dần", "Kỷ Mão"], ["Bính Tuất", "Đinh Hợi"]];
        //BANG TIEUTHANH DAITHANH CANCHI

        //NHITHAPBATTU
        self::$NHITHAPBATTULINHVAT = [["Cáo", "Én", "Quạ", "Hươu"], ["Hổ", "Lợn", "Khỉ", "Rắn"], ["Báo", "Nhím", "Vượn", "Giun"], ["Cá Sấu", "Cua", "Chó Sói", "Bò"], ["Rồng", "Trâu", "Chó", "Dê"], ["Lạc Đà", "Dơi", "Trĩ", "Cheo leo"], ["Thỏ", "Chuột", "Gà", "Ngựa"]];

        self::$NHITHAPBATTU = [["Tâm", "Nguy", "Tất", "Trương"], ["Vĩ", "Thất", "Chủy", "Dực"], ["Cơ", "Bích", "Sâm", "Chẩn"], ["Giác", "Đẩu", "Khuê", "Tỉnh"], ["Cang", "Ngưu", "Lâu", "Quỷ"], ["Đê", "Nữ", "Vị", "Liễu"], ["Phòng", "Hư", "Mão", "Tinh"]];

        self::$NHITHAPBATTUNGUHANH = [["Nguyệt", "Nguyệt", "Nguyệt", "Nguyệt"], ["Hỏa", "Hỏa", "Hỏa", "Hỏa"], ["Thủy", "Thủy", "Thủy", "Thủy"], ["Mộc", "Mộc", "Mộc", "Mộc"], ["Kim", "Kim", "Kim", "Kim"], ["Thổ", "Thổ", "Thổ", "Thổ"], ["Nhật", "Nhật", "Nhật", "Nhật"]];

        self::$NHITHAPBATTULINHVATHANNGU = [["Hồ", "Yến", "Ô", "Lộc"], ["Hổ", "Trư", "Hầu", "Xà"], ["Báo", "Du", "Viên", "Dẫn"], ["Giao", "Giải", "Lang", "Hãn"], ["Long", "Ngưu", "Cẩu", "Dương"], ["Lạc", "Bức", "Trĩ", "Chương"], ["Thố", "Thử", "Kê", "Mã"]];

        self::$NHITHAPBATTUTOTXAU = [["Xấu mọi việc", "Xấu nhiều tốt ít", "Tốt mọi việc", "Tốt mọi việc"], ["Xấu mọi việc", "Tốt mọi việc", "Tốt mọi việc", "Tốt mọi việc"], ["Hôn nhân, tu tạo xấu", "Tốt mọi việc", "Tốt mọi việc", "Xấu mọi việc"], ["Hôn nhân, tế tự, mai táng xấu", "Xấu mọi việc", "Tốt mọi việc", "Xấu mọi việc"], ["Hôn nhân, tế tự, mai táng xấu", "Xấu mọi việc", "Tốt mọi việc", "Xấu mọi việc"], ["Tốt tăng tài lộc", "Tốt mọi việc", "Xấu mọi việc", "Tốt mọi việc"], ["Mọi việc bất lợi", "Tốt mọi việc", "Tốt mọi việc", "Xấu nhiều tốt ít"]];
        self::$NHITHAPBATTUDAY = ["Giác"/*Thứ 5*/, "Cang", "Đê", "Phòng", "Tâm", "Vĩ", "Cơ", "Đẩu", "Ngưu", "Nữ", "Hư", "Nguy", "Thất", "Bích", "Khuê", "Lâu", "Vị", "Mão", "Tất", "Chủy", "Sâm", "Tỉnh", "Quỷ", "Liễu", "Tinh", "Trương", "Dực", "Chẩn"];
        self::$DAYNAME = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];

        //NHITHAPBATTU
        self::$NGAYLUCNHAMS = ["Đại An", "Lưu Liên", "Tốc Hỷ", "Xích Khẩu", "Tiểu Cát", "Không Vong"];

        self::$pxg_geofesh_data = new \stdClass();

        $data_thapthan = OptionRepository::get('pxg_geofesh_thapthan', null, 'polyfengshui');
        $data_thapnhitruc = OptionRepository::get('pxg_geofesh_thapnhitruc', null, 'polyfengshui');
        $data_dategoodbad = OptionRepository::get('pxg_geofesh_date_goodbad', null, 'polyfengshui');
        $data_nhithapbattu = OptionRepository::get('pxg_geofesh_nhithapbattu', null, 'polyfengshui');
        $data_tietkhi = OptionRepository::get('pxg_geofesh_tietkhi', null, 'polyfengshui');
        $data_cuucungphitinh = OptionRepository::get('pxg_geofesh_cucungphitinh', null, 'polyfengshui');

        $data_lucnham = OptionRepository::get('pxg_geofesh_lucnham', null, 'polyfengshui');
        $data_info_default = OptionRepository::get('pxg_geofesh_info_extend', null, 'polyfengshui');
        $data_hoangdaohacdao = OptionRepository::get('pxg_geofesh_hoangdaohacdao', null, 'polyfengshui') || self::$KIETHUNGNHATTOTXAU;
        //Settings default
        $data_settings_default = OptionRepository::get('pxg_geofesh_settings_default', null, 'polyfengshui');
        $data_thansat = OptionRepository::get('pxg_geofesh_thansat', null, 'polyfengshui');
        $data_compass = OptionRepository::get('pxg_geofesh_compass', null, 'polyfengshui');
        $data_truongsinh = OptionRepository::get('pxg_geofesh_truongsinh', null, 'polyfengshui');
        $data_napam_tieuthanh = OptionRepository::get('pxg_geofesh_napam', null, 'polyfengshui');
        $data_napam_daithanh = OptionRepository::get('pxg_geofesh_napam_daithanh', null, 'polyfengshui');
        //Đổng Công Trạch Nhật
        $arr_dongcong = [];
        for ($i = 1; $i <= 12; $i++) {
            $objs = OptionRepository::get('pxg_geofesh_dongcong' . $i, [], 'polyfengshui');
            $arr_dongcong[] = (!empty($objs['pxg_geofesh_dongcong' . $i]) ? json_decode($objs['pxg_geofesh_dongcong' . $i]) : []);
        }
        self::$pxg_geofesh_data->dongcong = $arr_dongcong;
        
        if (!empty($data_settings_default['pxg_geofesh_lasotutru_name'])) {
            self::$pxg_geofesh_data->lasotutru_name_default = $data_settings_default['pxg_geofesh_lasotutru_name'];
        }
        //Settings default
            self::$pxg_geofesh_data->hoangdaohacdao =  $data_hoangdaohacdao['pxg_geofesh_hoangdaohacdao'] ?? self::$KIETHUNGNHATTOTXAU;//$data_hoangdaohacdao;//json_decode($data_hoangdaohacdao['pxg_geofesh_hoangdaohacdao']);
        if (!empty($data_info_default['pxg_geofesh_info_extend'])) {
            self::$pxg_geofesh_data->info_default = json_decode($data_info_default['pxg_geofesh_info_extend']);
        }
        if (!empty($data_lucnham['pxg_geofesh_lucnham'])) {
            self::$pxg_geofesh_data->lucnham = json_decode($data_lucnham['pxg_geofesh_lucnham']);
        }
        if (!empty($data_tietkhi['pxg_geofesh_tietkhi'])) {
            self::$pxg_geofesh_data->tietkhi = json_decode($data_tietkhi['pxg_geofesh_tietkhi']);
        }
        if (!empty($data_thapthan['pxg_geofesh_thapthan'])) {
            self::$pxg_geofesh_data->thapthan = json_decode($data_thapthan['pxg_geofesh_thapthan']);
        }
        if (!empty($data_nhithapbattu['pxg_geofesh_nhithapbattu'])) {
            self::$pxg_geofesh_data->nhithapbattu = json_decode($data_nhithapbattu['pxg_geofesh_nhithapbattu']);
        }
        if (!empty($data_thapnhitruc['pxg_geofesh_thapnhitruc'])) {
            self::$pxg_geofesh_data->thapnhitruc = json_decode($data_thapnhitruc['pxg_geofesh_thapnhitruc']);
        }
        if (!empty($data_dategoodbad['pxg_geofesh_date_goodbad'])) {
            self::$pxg_geofesh_data->dategoodbad = json_decode($data_dategoodbad['pxg_geofesh_date_goodbad']);
        }
        if (!empty($data_cuucungphitinh['pxg_geofesh_cucungphitinh'])) {
            self::$pxg_geofesh_data->cuucungphitinh = json_decode($data_cuucungphitinh['pxg_geofesh_cucungphitinh']);
        }
        if (!empty($data_thansat['pxg_geofesh_thansat'])) {
            self::$pxg_geofesh_data->thansat = json_decode($data_thansat['pxg_geofesh_thansat']);
        }
        if (!empty($data_compass['pxg_geofesh_compass'])) {
            self::$pxg_geofesh_data->compass = json_decode($data_compass['pxg_geofesh_compass']);
        }
        if (!empty($data_truongsinh['pxg_geofesh_truongsinh'])) {
            self::$pxg_geofesh_data->truongsinh = json_decode($data_truongsinh['pxg_geofesh_truongsinh']);
        }
        $arr_napam = [];
        if (!empty($data_napam_tieuthanh['pxg_geofesh_napam'])) {
            $arr_napam = json_decode($data_napam_tieuthanh['pxg_geofesh_napam']);
        }
        if (!empty($data_napam_daithanh['pxg_geofesh_napam_daithanh'])) {
            $arr_napam = array_merge($arr_napam, json_decode($data_napam_daithanh['pxg_geofesh_napam_daithanh']));
        }
        self::$pxg_geofesh_data->napam = $arr_napam;
        //var_export(self::$pxg_geofesh_data->thapnhitruc);
        self::$PI = pi();
        add_action('wp_ajax_pxg_geofesh_call', array($this, 'pxg_geofesh_call'));
        add_action('wp_ajax_nopriv_pxg_geofesh_call', array($this, 'pxg_geofesh_call'));

        add_action('wp_ajax_pxg_geofesh_sdaivan_call', array($this, 'pxg_geofesh_sdaivan_call'));
        add_action('wp_ajax_nopriv_pxg_geofesh_sdaivan_call', array($this, 'pxg_geofesh_sdaivan_call'));

        add_action('wp_ajax_pxg_geofesh_date_call', array($this, 'pxg_geofesh_date_call'));
        add_action('wp_ajax_nopriv_pxg_geofesh_date_call', array($this, 'pxg_geofesh_date_call'));

        add_action('wp_ajax_pxg_geofesh_date_convert', array($this, 'pxg_geofesh_date_convert'));
        add_action('wp_ajax_nopriv_pxg_geofesh_date_convert', array($this, 'pxg_geofesh_date_convert'));

        //DATE Good Bad Opti
        add_action('init', array($this, 'pxg_geofesh_date_rewrite_rule'));
        add_filter('query_vars', array($this, 'pxg_geofesh_date_query_vars_filter'), 1);

        if ($this->PXG_IS_SEO_RANK_MATH_ACTIVE()) {
            add_filter('rank_math/frontend/description', array($this, 'pxg_geofesh_seo_meta_description'));
        } else { // Default + YOAST SEO
            add_filter('pre_get_document_title', array($this, 'pxg_geofesh_date_optimization_title_filter'), 10, 3);

            // Merge YOAST SEO;
            add_filter('wpseo_opengraph_title', array($this, 'pxg_geofesh_date_optimization_title_filter'), 10, 1); // Meta og:title
            add_filter('wpseo_opengraph_desc', array($this, 'pxg_geofesh_seo_meta_description'), 10, 1); // Meta og:description

            add_filter('wpseo_title', array($this, 'pxg_geofesh_date_optimization_title_filter'), 10, 3);
            add_filter('wpseo_metadesc', array($this, 'pxg_geofesh_seo_meta_description'), 10, 1);

            add_filter('wpseo_schema_webpage', array($this, 'pxg_geofesh_date_customize_yoast_schema_data'), 100, 2); // Geofesh date opti
        }
        //DATE Good Bad Opti
    }

    /**
     * Get the single instance of the Date class.
     *
     * @return Date
     */
    public static function getInstance(): Date
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function pxg_reg_shortcodes()
    {
        add_shortcode('pxg_geofesh', array($this, 'pxg_geofesh'));
        add_shortcode('pxg_geofesh_date', array($this, 'pxg_geofesh_date'));
        add_shortcode('pxg_geofeshx_date', array($this, 'pxg_geofeshx_date'));
        add_shortcode('pxg_geofeshx_date_tamnguyen', array($this, 'pxg_geofeshx_date_tamnguyen'));

        add_shortcode('pxg_geofesh_date_info', array($this, 'pxg_geofesh_date_info'));

        add_shortcode('pxg_geofesh_test', array($this, 'pxg_geofesh_test'));
        add_shortcode('pxg_geofesh_solar', array($this, 'pxg_geofesh_solar'));

        //DATA
        add_shortcode('pxg_geofesh_data', array($this, 'pxg_geofesh_data_fnc'));
    }

    #PXG SEO Opti;
    public function pxg_geofesh_date_customize_yoast_schema_data($pieces, $context)
    {
        $gefesh_date = get_query_var('pagename');
        $geofesh_date_slug = self::$pxg_geofesh_data->geofesh_date_slug;
        if ($gefesh_date === $geofesh_date_slug) {
            $dayx = get_query_var('dayx');
            $monthx = get_query_var('monthx');
            $yearx = get_query_var('yearx');
            $meta_title_rest = '';
            if (!empty($dayx) && !empty($monthx) && !empty($yearx)) {
                $meta_pattern = self::$pxg_geofesh_data->geofesh_date_meta_seo_title;
                $meta_title_rest = str_replace('{solar_day}', $dayx, $meta_pattern);
                $meta_title_rest = str_replace('{solar_month}', $monthx, $meta_title_rest);
                $meta_title_rest = str_replace('{solar_year}', $yearx, $meta_title_rest);
                $pieces['name'] = $meta_title_rest;
            }
            return $pieces;
        }
    }

    public function pxg_geofesh_seo_meta_description($description)
    {
        $gefesh_date = get_query_var('pagename');
        $geofesh_date_slug = self::$pxg_geofesh_data->geofesh_date_slug;
        if ($gefesh_date === $geofesh_date_slug) {
            $dayx = get_query_var('dayx');
            $monthx = get_query_var('monthx');
            $yearx = get_query_var('yearx');
            if (!empty($dayx) && !empty($monthx) && !empty($yearx)) {
                $lunarDate = $this->getLunarDate($dayx, $monthx, $yearx);
                $meta_pattern = self::$pxg_geofesh_data->geofesh_date_meta_seo_description;
                $content_rest = str_replace('{solar_day}', $dayx, $meta_pattern);
                $content_rest = str_replace('{solar_month}', $monthx, $content_rest);
                $content_rest = str_replace('{solar_year}', $yearx, $content_rest);
                if ($lunarDate) {
                    $_dayCanChi = $this->getDayCanChi($lunarDate->jd);
                    $NONGLICHX = $this->pxg_get_month_NONGLICH_TIETKHI($dayx, $monthx, $yearx);
                    $day_name = $this->pxg_geofesh_get_day_name($yearx, $monthx, $dayx);

                    $content_rest = str_replace('{lunar_day}', $lunarDate->day, $content_rest);
                    $content_rest = str_replace('{lunar_month}', $lunarDate->month, $content_rest);
                    $content_rest = str_replace('{lunar_year}', $lunarDate->year, $content_rest);
                    $content_rest = str_replace('{day_name}', "{$day_name}", $content_rest);
                    $content_rest = str_replace('{lunar_can_chi_day}', "{$_dayCanChi}", $content_rest);
                    $content_rest = str_replace('{lunar_can_chi_month}', "{$NONGLICHX->month_can} {$NONGLICHX->month_chi}", $content_rest);
                    $content_rest = str_replace('{lunar_can_chi_year}', "{$NONGLICHX->year_can} {$NONGLICHX->year_chi}", $content_rest);
                }
                return $content_rest;
            }
        }
        return $description;
    }

    public function pxg_geofesh_date_optimization_title_filter($title)
    {
        $gefesh_date = get_query_var('pagename');
        $geofesh_date_slug = self::$pxg_geofesh_data->geofesh_date_slug;
        if ($gefesh_date === $geofesh_date_slug) {
            $dayx = get_query_var('dayx');
            $monthx = get_query_var('monthx');
            $yearx = get_query_var('yearx');
            if (!empty($dayx) && !empty($monthx) && !empty($yearx)) {
                $lunarDate = $this->getLunarDate($dayx, $monthx, $yearx);
                $meta_pattern = self::$pxg_geofesh_data->geofesh_date_meta_seo_title;
                $content_rest = str_replace('{solar_day}', $dayx, $meta_pattern);
                $content_rest = str_replace('{solar_month}', $monthx, $content_rest);
                $content_rest = str_replace('{solar_year}', $yearx, $content_rest);
                if ($lunarDate) {
                    $_dayCanChi = $this->getDayCanChi($lunarDate->jd);
                    $NONGLICHX = $this->pxg_get_month_NONGLICH_TIETKHI($dayx, $monthx, $yearx);
                    $day_name = $this->pxg_geofesh_get_day_name($yearx, $monthx, $dayx);

                    $content_rest = str_replace('{lunar_day}', $lunarDate->day, $content_rest);
                    $content_rest = str_replace('{lunar_month}', $lunarDate->month, $content_rest);
                    $content_rest = str_replace('{lunar_year}', $lunarDate->year, $content_rest);
                    $content_rest = str_replace('{day_name}', "{$day_name}", $content_rest);
                    $content_rest = str_replace('{lunar_can_chi_day}', "{$_dayCanChi}", $content_rest);
                    $content_rest = str_replace('{lunar_can_chi_month}', "{$NONGLICHX->month_can} {$NONGLICHX->month_chi}", $content_rest);
                    $content_rest = str_replace('{lunar_can_chi_year}', "{$NONGLICHX->year_can} {$NONGLICHX->year_chi}", $content_rest);
                }
                return $content_rest;
            }
        }
        return $title;
    }
    public function pxg_geofesh_date_rewrite_rule()
    {
        add_rewrite_tag('%dayx%', '([^&]+)');
        add_rewrite_tag('%monthx%', '([^&]+)');
        add_rewrite_tag('%yearx%', '([^&]+)');
        $date_rule = self::$pxg_geofesh_data->geofesh_rewrite_date_rule;
        $date_rule = str_replace('{day}', '([0-9]+)', $date_rule);
        $date_rule = str_replace('{month}', '([0-9]+)', $date_rule);
        $date_rule = str_replace('{year}', '([0-9]+)', $date_rule);
        $slug = self::$pxg_geofesh_data->geofesh_date_slug;
        add_rewrite_rule('^' . $slug . '/' . $date_rule . '/?$', 'index.php?pagename=' . $slug . '&dayx=$matches[1]&monthx=$matches[2]&yearx=$matches[3]', 'top');
    }
    public function pxg_geofesh_date_query_vars_filter($vars)
    {
        array_push($vars, 'dayx');
        array_push($vars, 'monthx');
        array_push($vars, 'yearx');
        return $vars;
    }
    #PXG SEO Opti;

    public function pxg_get_chi_TUONGTINH($_chi)
    {
        if ($_chi == 'Dần' || $_chi == 'Ngọ' || $_chi == 'Tuất') {
            return 'Ngọ';
        } else if ($_chi == 'Thân' || $_chi == 'Tý' || $_chi == 'Thìn') {
            return 'Tý';
        } else if ($_chi == 'Hợi' || $_chi == 'Mão' || $_chi == 'Mùi') {
            return 'Mão';
        } else if ($_chi == 'Tỵ' || $_chi == 'Dậu' || $_chi == 'Sửu') {
            return 'Dậu';
        }
        return '';
    }
    public function pxg_get_chi_COTHAN($_chi)
    {
        if ($_chi == 'Hợi' || $_chi == 'Tý' || $_chi == 'Sửu') {
            return 'Dần';
        } else if ($_chi == 'Dần' || $_chi == 'Mão' || $_chi == 'Thìn') {
            return 'Tỵ';
        } else if ($_chi == 'Tỵ' || $_chi == 'Ngọ' || $_chi == 'Mùi') {
            return 'Thân';
        } else if ($_chi == 'Thân' || $_chi == 'Dậu' || $_chi == 'Tuất') {
            return 'Hợi';
        }
        return '';
    }
    public function pxg_get_chi_QUATU($_chi)
    {
        if ($_chi == 'Hợi' || $_chi == 'Tý' || $_chi == 'Sửu') {
            return 'Tuất';
        } else if ($_chi == 'Dần' || $_chi == 'Mão' || $_chi == 'Thìn') {
            return 'Sửu';
        } else if ($_chi == 'Tỵ' || $_chi == 'Ngọ' || $_chi == 'Mùi') {
            return 'Thìn';
        } else if ($_chi == 'Thân' || $_chi == 'Dậu' || $_chi == 'Tuất') {
            return 'Mùi';
        }
        return '';
    }
    public function pxg_get_chi_THIENMA($_chi)
    {
        if ($_chi == 'Thân' || $_chi == 'Tý' || $_chi == 'Thìn') {
            return 'Dần';
        } else if ($_chi == 'Hợi' || $_chi == 'Mão' || $_chi == 'Mùi') {
            return 'Tỵ';
        } else if ($_chi == 'Dần' || $_chi == 'Ngọ' || $_chi == 'Tuất') {
            return 'Thân';
        } else if ($_chi == 'Tỵ' || $_chi == 'Dậu' || $_chi == 'Sửu') {
            return 'Hợi';
        }
        return '';
    }
    public function pxg_get_chi_DAOHOA($_chi)
    {
        if ($_chi == 'Thân' || $_chi == 'Tý' || $_chi == 'Thìn') {
            return 'Dậu';
        } else if ($_chi == 'Hợi' || $_chi == 'Mão' || $_chi == 'Mùi') {
            return 'Tý';
        } else if ($_chi == 'Dần' || $_chi == 'Ngọ' || $_chi == 'Tuất') {
            return 'Mão';
        } else if ($_chi == 'Tỵ' || $_chi == 'Dậu' || $_chi == 'Sửu') {
            return 'Ngọ';
        }
        return '';
    }
    public function pxg_get_chi_QUYNHAN($_can)
    {
        if ($_can == 'Giáp' || $_can == 'Mậu' || $_can == 'Canh') {
            return 'Sửu, Mùi';
        } else if ($_can == 'Ất' || $_can == 'Kỷ') {
            return 'Tý, Thân';
        } else if ($_can == 'Bính' || $_can == 'Đinh') {
            return 'Hợi, Dậu';
        } else if ($_can == 'Nhâm' || $_can == 'Quý') {
            return 'Mão, Tỵ';
        } else if ($_can == 'Tân') {
            return 'Ngọ, Dần';
        }
        return '';
    }
    //#region GEO
    public function LunarDate($dd, $mm, $yy, $leap, $jd)
    {
        $z = new \stdClass;
        $z->day = intval($dd);
        $z->month = intval($mm);
        $z->year = intval($yy);
        $z->leap = $leap;
        $z->jd = $jd;
        return $z;
    }
    public function getLunarDate($dd, $mm, $yyyy)
    {
        if ($yyyy < 1200 || 2199 < $yyyy) {
            return $this->LunarDate(0, 0, 0, 0, 0);
        }
        $ly = $this->getYearInfo($yyyy);
        $jd = $this->jdn($dd, $mm, $yyyy);
        if ($jd < $ly[0]->jd) {
            $ly = $this->getYearInfo($yyyy - 1);
        }
        return $this->findLunarDate($jd, $ly);
    }
    public function findLunarDate($jd, $ly)
    {
        if ($jd > self::$LAST_DAY || $jd < self::$FIRST_DAY || $ly[0]->jd > $jd) {
            return $this->LunarDate(0, 0, 0, 0, $jd);
        }
        $i = count($ly) - 1;
        while ($jd < $ly[$i]->jd) {
            $i--;
        }
        $off = $jd - $ly[$i]->jd;
        $ret = $this->LunarDate($ly[$i]->day + $off, $ly[$i]->month, $ly[$i]->year, $ly[$i]->leap, $jd);
        return $ret;
    }
    public function jdn($dd, $mm, $yy)
    {
        $a = $this->INT((14 - $mm) / 12);
        $y = $yy + 4800 - $a;
        $m = $mm + 12 * $a - 3;
        $jd = $dd + $this->INT((153 * $m + 2) / 5) + 365 * $y + $this->INT($y / 4) - $this->INT($y / 100) + $this->INT($y / 400) - 32045;
        if ($jd < 2299161) {
            $jd = $dd + $this->INT((153 * $m + 2) / 5) + 365 * $y + $this->INT($y / 4) - 32083;
        }
        return $jd;
    }
    public function getYearInfo($yyyy)
    {
        //$yearCode = self::$TK22[$yyyy - 2100];
        if ($yyyy < 1300) {
            $yearCode = self::$TK13[$yyyy - 1200];
        } else if ($yyyy < 1400) {
            $yearCode = self::$TK14[$yyyy - 1300];
        } else if ($yyyy < 1500) {
            $yearCode = self::$TK15[$yyyy - 1400];
        } else if ($yyyy < 1600) {
            $yearCode = self::$TK16[$yyyy - 1500];
        } else if ($yyyy < 1700) {
            $yearCode = self::$TK17[$yyyy - 1600];
        } else if ($yyyy < 1800) {
            $yearCode = self::$TK18[$yyyy - 1700];
        } else if ($yyyy < 1900) {
            $yearCode = self::$TK19[$yyyy - 1800];
        } else if ($yyyy < 2000) {
            $yearCode = self::$TK20[$yyyy - 1900];
        } else if ($yyyy < 2100) {
            $yearCode = self::$TK21[$yyyy - 2000];
        } else {
            $yearCode = self::$TK22[$yyyy - 2100];
        }
        return $this->decodeLunarYear($yyyy, $yearCode);
    }
    public function decodeLunarYear($yy, $k)
    {
        $offsetOfTet = $leapMonth = $leapMonthLength = $solarNY = $currentJD = $j = $mm = null;
        $ly = [];
        $monthLengths = [29, 30];
        $regularMonths = [12];
        $offsetOfTet = $k >> 17;
        $leapMonth = $k & 0xf;
        $leapMonthLength = $monthLengths[$k >> 16 & 0x1];
        $solarNY = $this->jdn(1, 1, $yy);
        $currentJD = $solarNY + $offsetOfTet;
        $j = $k >> 4;
        for ($i = 0; $i < 12; $i++) {
            $regularMonths[12 - $i - 1] = $monthLengths[$j & 0x1];
            $j >>= 1;
        }
        if ($leapMonth == 0) {
            for ($mm = 1; $mm <= 12; $mm++) {
                array_push($ly, $this->LunarDate(1, $mm, $yy, 0, $currentJD));
                $currentJD += $regularMonths[$mm - 1];
            }
        } else {
            for ($mm = 1; $mm <= $leapMonth; $mm++) {
                array_push($ly, $this->LunarDate(1, $mm, $yy, 0, $currentJD));
                $currentJD += $regularMonths[$mm - 1];
            }
            array_push($ly, $this->LunarDate(1, $leapMonth, $yy, 1, $currentJD));
            $currentJD += $leapMonthLength;
            for ($mm = $leapMonth + 1; $mm <= 12; $mm++) {
                array_push($ly, $this->LunarDate(1, $mm, $yy, 0, $currentJD));
                $currentJD += $regularMonths[$mm - 1];
            }
        }
        return $ly;
    }
    //#endregion GEO

    public function pxg_geofesh_YEAR_DAIVAN($_daySolar, $_monthSolar, $_yearSolar, $_yearSolarMain, $_hour, $_minute, $is_fee = true)
    {

        $CURYEAR = (!empty($_yearSolar) ? $_yearSolar : intval(date("Y")));
        $CURYEARMAIN = (!empty($_yearSolarMain) ? $_yearSolarMain : intval(date("Y")));

        $_can_gio = $this->getCanHour($_daySolar, $_monthSolar, $CURYEAR, $_hour);
        $_chi_gio = $this->getChiHour($_hour);

        $ld_curyear = $this->getLunarDate($_daySolar, $_monthSolar, $CURYEAR); //Current year;
        $ld_curyear_main = $this->getLunarDate($_daySolar, $_monthSolar, $CURYEARMAIN); //Year of main;

        $_dayCanChi = $this->getDayCanChi($ld_curyear_main->jd);

        $CAN_CURYEAR_DAIVAN = $this->getYearCan($ld_curyear->year);
        $CHI_CURYEAR_DAIVAN = $this->getYearChi($ld_curyear->year);
        $CHI_CURDAY = $this->getDayChi($ld_curyear->jd);

        $CAN_CURDAY = $this->getDayCan($ld_curyear_main->jd);

        $CAN_CURMONTH = $this->getMonthCan($ld_curyear->month, $ld_curyear->year);
        $CHI_CURMONTH = $this->getMonthChi($ld_curyear->month);
        $THAPTHAN_CURYEAR = $this->pxg_get_BANGTHAPTHAN($CAN_CURDAY, $CAN_CURYEAR_DAIVAN);
        $CAN_NGUHANH_CURYEAR = $this->pxg_get_ngu_hanh_by_can($CAN_CURYEAR_DAIVAN);
        $CHI_NGUHANH_CURYEAR = $this->pxg_get_ngu_hanh_by_chi($CHI_CURYEAR_DAIVAN);
        $TANGCAN_CURYEAR = $this->pxg_get_tang_can_dia_chi_thap_than($CHI_CURYEAR_DAIVAN, $CAN_CURDAY);
        $NAPAM_CURYEAR = [];
        $VONGTRUONGSINH_CURYEAR = [];
        $THANSAT_CURYEAR = [];
        if ($is_fee == true) {
            $NAPAM_CURYEAR = $this->pxg_geofesh_date_NAPAM($CAN_CURYEAR_DAIVAN, $CHI_CURYEAR_DAIVAN);
            $VONGTRUONGSINH_CURYEAR = $this->pxg_geofesh_VONGTRUONGSINH($CAN_CURDAY, $CHI_CURYEAR_DAIVAN);
            $THANSAT_CURYEAR = $this->pxg_geofesh_THANSAT_EXT($CHI_CURYEAR_DAIVAN, $CHI_CURDAY, $CHI_CURMONTH, $_chi_gio, $CAN_CURYEAR_DAIVAN, $CAN_CURDAY, $CAN_CURMONTH, $_can_gio);
        }
        //DAIVAN_CURYEAR
        $DAIVAN_CURYEAR = array('can' => $CAN_CURYEAR_DAIVAN, 'chi' => $CHI_CURYEAR_DAIVAN, 'tangcan' => $TANGCAN_CURYEAR, 'napam' => $NAPAM_CURYEAR, 'thansat' => $THANSAT_CURYEAR->year, 'vongtruongsinh' => $VONGTRUONGSINH_CURYEAR, 'year_can_thap_than' => $THAPTHAN_CURYEAR, 'year_can_nguhanh' => $CAN_NGUHANH_CURYEAR, 'year_chi_nguhanh' => $CHI_NGUHANH_CURYEAR);
        return $DAIVAN_CURYEAR;
    }
    public function pxg_get_month_NONGLICH_TIETKHI($day, $month, $year, $hour = 0, $minute = 0)
    {
        $ld = $this->getLunarDate($day, $month, $year);
        $_tietKhi = $this->get_TietKhi_by_time($day, $month, $year, $hour, $minute, 0);
        $_monthNongLich = $this->pxg_get_id_arr2_str(self::$TIET12KHI12, $_tietKhi);
        if ($_monthNongLich == 11 && $year > $ld->year) {
            $year = $year - 1;
        }
        $_monthNongLichCanChi = $this->getMonthCanChi($_monthNongLich, $year);
        $_arr = explode(' ', $_monthNongLichCanChi);
        $_monthNongLichCan = $_arr[0];
        $_monthNongLichChi = $_arr[1];

        if ($_monthNongLich == 0 && $year > $ld->year) {
            $year = $year - 1;
        }
        $_yearNongLichCanChi = $this->getYearCanChi($year);
        $_arr2 = explode(' ', $_yearNongLichCanChi);
        $_yearNongLichCan = $_arr2[0];
        $_yearNongLichChi = $_arr2[1];
        return (object)array('month' => $_monthNongLich, 'year' => $year, 'tietkhi' => $_tietKhi, 'month_can' => $_monthNongLichCan, 'month_chi' => $_monthNongLichChi, 'month_canchi' => $_monthNongLichCanChi, 'year_can' => $_yearNongLichCan, 'year_chi' => $_yearNongLichChi, 'year_canchi' => $_yearNongLichCanChi);
    }
    public static function pxg_geofesh_get_sites_target()
    {
        $options = OptionRepository::get('pxg_geofesh_defaults', [], 'polyfengshui');
        $x = $options['pxg_geofesh_site_taget_ids'];
        return ((!empty($x)) ? json_decode($x) : '');
    }
    public static function pxg_pxgshortlinks_defaults_get_option($option_name)
    {
        $options = OptionRepository::get('pxgshortlinks_defaults', [], 'polyfengshui');
        if (!empty($options)) {
            return $options[$option_name];
        }
        return '';
    }
    public function pxg_get_last_no_geo($_n, $_f)
    {/*Tính số cuối cùng theo _f*/
        $_str_no = $_n . '';
        $total = 0;
        for ($i = 0; $i < strlen($_str_no); $i++) {
            $total += intval($_str_no[$i]);
        }
        if ($total > $_f) {
            $total = $this->pxg_get_last_no_geo($total, $_f);
        }
        return $total;
    }
    public function pxg_get_last_no_2_geo($_n, $_f)
    {/*Tính số cuối cùng theo _f*/
        $_str_no = $_n . '';
        $total = intval($_str_no[strlen($_str_no) - 1]) + intval($_str_no[strlen($_str_no) - 2]);
        if ($total > $_f) {
            $total = $this->pxg_get_last_no_geo($total, $_f);
        }
        return $total;
    }
    public function pxg_get_cung_phi($_year, $_sez)
    {/*Cung Phi idx theo năm và giới tính*/
        $_lastNo = $this->pxg_get_last_no_2_geo($_year, 9);
        $_compassNo = '';
        if ($_year < 2000) {
            if ($_sez == 'male') {/*nam*/
                $_compassNo = 10 - $_lastNo;
                if ($_compassNo == 5) {
                    $_compassNo = 2;
                }
            } else {/*nữ*/
                $_compassNo = 5 + $_lastNo;
                if ($_compassNo > 9) {
                    $_compassNo = $this->pxg_get_last_no_geo($_compassNo, 9);
                }
                if ($_compassNo == 5) {
                    $_compassNo = 8;
                }
            }
        } else {
            if ($_sez == 'male') {/*nam*/
                $_compassNo = 9 - $_lastNo;
                if ($_compassNo == 0) {
                    $_compassNo = 9;
                }
                if ($_compassNo == 5) {
                    $_compassNo = 2;
                }
            } else {/*nữ*/
                $_compassNo = 6 + $_lastNo;
                if ($_compassNo > 9) {
                    $_compassNo = $this->pxg_get_last_no_geo($_compassNo, 9);
                }
                if ($_compassNo == 5) {
                    $_compassNo = 8;
                }
            }
        }
        /*if($_sez=='male'){
      $_compassNo=11-$_lastNo;
      if($_compassNo>9){
        $_compassNo=$this->pxg_get_last_no_geo($_compassNo,9);
      }
      if($_compassNo==5){
        $_compassNo=2;
      }
    }else{
      $_compassNo=4+$_lastNo;
      if($_compassNo>9){
        $_compassNo=$this->pxg_get_last_no_geo($_compassNo,9);
      }
      if($_compassNo==5){
        $_compassNo=8;
      }
    }*/
        return $_compassNo;
    }
    public function pxg_get_id_arr($_arr, $_n)
    {
        for ($i = 0; $i < count($_arr); $i++) {
            if ($_n == $_arr[$i]) {
                return $i;
            }
        };
        return '';
    }
    public function pxg_get_id_arr_str($arr, $str)
    {
        for ($i = 0; $i < count($arr); $i++) {
            if (stripos($arr[$i], $str) !== false) {
                return $i;
                break;
            }
        }
        return -1;
    }
    public function pxg_get_id_arr2_str($arr, $str)
    {
        for ($j = 0; $j < count($arr); $j++) {
            for ($i = 0; $i < count($arr[$j]); $i++) {
                if (stripos($arr[$j][$i], $str) !== false) {
                    return $j;
                    break;
                }
            }
        }
        return -1;
    }
    public function pxg_get_id_arr2($arr, $id)
    {
        for ($j = 0; $j < count($arr); $j++) {
            for ($i = 0; $i < count($arr[$j]); $i++) {
                if ($arr[$j][$i] == $id) {
                    return $j;
                    break;
                }
            }
        }
        return -1;
    }
    //TODOTEST
    public function pxg_geofesh_test()
    {
        /*$year=1990;
    $male_compassNo=$this->pxg_get_cung_phi($year,'male');
    $_CUNGI=$male_compassNo-1;
    $TuyetMenhI=self::pxg_get_id_arr(self::$arrCompassCung,self::$TuyetMenh[$_CUNGI]);
    //self::$QUEBATQUAI=["Khảm","Khôn","Chấn","Tốn","5:2/8","Càn","Đoài","Cấn","Ly"];
    echo $year.' Tuyệt Mệnh: '.self::$QUEBATQUAI[$TuyetMenhI].' IDX: '.$_CUNGI;*/

        /*$_can_gio='Mậu';
    $_chi_gio='Dần';
    $_dayCan='Canh';
    $_dayChi='Tý';
    $_monthCan='Giáp';
    $_monthChi='Thìn';
    $_yearChi='Dần';
    $_yearCan='Nhâm';

    $x=$this->pxg_geofesh_KHONGVONG_EXT($_yearChi,$_dayChi,$_monthChi,$_chi_gio,$_yearCan,$_dayCan,$_monthCan,$_can_gio);
    var_dump($x);*/
    }
    public function pxg_geofesh_sdaivan_call($args)
    {
        $_daySolar = isset($_POST['d']) ? $_POST['d'] : (isset($args['day']) ? $args['day'] : '');
        $_monthSolar = isset($_POST['m']) ? $_POST['m'] : (isset($args['month']) ? $args['month'] : '');/*from 0-11*/
        $_yearSolar = isset($_POST['y']) ? $_POST['y'] : (isset($args['year']) ? $args['year'] : '');
        $_yearSolarMain = isset($_POST['ym']) ? $_POST['ym'] : (isset($args['year_main']) ? $args['year_main'] : '');
        $_hour = isset($_POST['h']) ? $_POST['h'] : (isset($args['hour']) ? $args['hour'] : '');
        $_minute = isset($_POST['mi']) ? $_POST['mi'] : (isset($args['mi']) ? $args['mi'] : '');
        if (!empty($_daySolar)) {
            $_daySolar = intval($_daySolar);
        } else {
            $_daySolar = intval(date('d'));
        }
        if (!empty($_monthSolar)) {
            $_monthSolar = intval($_monthSolar) + 1;
        } else {
            $_monthSolar = intval(date('m'));
        }
        if (!empty($_yearSolar)) {
            $_yearSolar = intval($_yearSolar);
        }
        if (!empty($_hour)) {
            $_hour = intval($_hour);
        }
        if (!empty($_minute)) {
            $_minute = intval($_minute);
        }
        $_tr = '';
        $DATA = $this->pxg_geofesh_YEAR_DAIVAN($_daySolar, $_monthSolar, $_yearSolar, $_yearSolarMain, $_hour, $_minute);
        $_tr .= json_encode($DATA);
        echo $_tr;
        if (isset($_POST['y'])) {
            exit();
        }
    }
    public function pxg_geofesh_call($args)
    {
        $_name = isset($_POST['n']) ? $_POST['n'] : (isset($args['name']) ? $args['name'] : '');
        $_sez = isset($_POST['s']) ? $_POST['s'] : (isset($args['sex']) ? $args['sex'] : '');
        $_daySolar = isset($_POST['d']) ? $_POST['d'] : (isset($args['day']) ? $args['day'] : '');
        $_monthSolar = isset($_POST['m']) ? $_POST['m'] : (isset($args['month']) ? $args['month'] : '');/*from 0-11*/
        $_yearSolar = isset($_POST['y']) ? $_POST['y'] : (isset($args['year']) ? $args['year'] : '');
        $_yearSolarMain = isset($_POST['ym']) ? $_POST['ym'] : (isset($args['year_main']) ? $args['year_main'] : '');
        $_hour = isset($_POST['h']) ? $_POST['h'] : (isset($args['hour']) ? $args['hour'] : '');
        $_minute = isset($_POST['mi']) ? $_POST['mi'] : (isset($args['minute']) ? $args['minute'] : '');
        if (!empty(self::$pxg_geofesh_data->is_fee) && self::$pxg_geofesh_data->is_fee == true) {
            self::$is_fee = true;
        } else {
            $current_user = wp_get_current_user();
            $allowed_roles = array('administrator');
            if (array_intersect($allowed_roles, $current_user->roles)) { //$current_user->id==1
                self::$is_fee = true;
            } else {
                $user_licenses = $this->pxg_geofesh_is_valid_license($current_user->id);
                if ((!empty($user_licenses)) && $user_licenses->apps[0]->is_active == true && strripos($user_licenses->apps[0]->apps, 'lasotutru') !== false) {
                    self::$is_fee = true;
                } else {
                    self::$is_fee = false;
                }
            }
        }
        $_monthNoName = 0;
        if (!empty($_daySolar)) {
            $_daySolar = intval($_daySolar);
        } else {
            $_daySolar = intval(date('d'));
        }
        if ($_monthSolar != '') {
            $_monthSolar = intval($_monthSolar) + 1;
        } else {
            $_monthSolar = intval(date('m'));
        }
        if (!empty($_yearSolar)) {
            $_yearSolar = intval($_yearSolar);
        }
        if (!empty($_hour)) {
            $_hour = intval($_hour);
        }
        if (!empty($_minute)) {
            $_minute = intval($_minute);
        }
        $_tr = '';
        $_compassNo = $this->pxg_get_cung_phi($_yearSolar, $_sez);
        $_CUNGI = $_compassNo - 1;

        $SinhKhiI = self::pxg_get_id_arr(self::$arrCompassCung, self::$SinhKhi[$_CUNGI]);
        $ThienYI = self::pxg_get_id_arr(self::$arrCompassCung, self::$ThienY[$_CUNGI]);
        $DienNienI = self::pxg_get_id_arr(self::$arrCompassCung, self::$DienNien[$_CUNGI]);
        $PhucViI = self::pxg_get_id_arr(self::$arrCompassCung, self::$PhucVi[$_CUNGI]);

        $HoaHaiI = self::pxg_get_id_arr(self::$arrCompassCung, self::$HoaHai[$_CUNGI]);
        $NguQuyI = self::pxg_get_id_arr(self::$arrCompassCung, self::$NguQuy[$_CUNGI]);
        $LucSatI = self::pxg_get_id_arr(self::$arrCompassCung, self::$LucSat[$_CUNGI]);
        $TuyetMenhI = self::pxg_get_id_arr(self::$arrCompassCung, self::$TuyetMenh[$_CUNGI]);

        $ld = $this->getLunarDate($_daySolar, $_monthSolar, $_yearSolar);

        $HUONGTOT = [];
        $HUONGXAU = [];
        $HUONGTOTXAU = [];

        $HUONGTOT[] = (object) array(
            'name' => 'Sinh Khí', 'compass' => self::$SinhKhi[$_CUNGI], 'zodiac' => self::$QUEBATQUAI[$SinhKhiI], 'five_elements' => self::$arrNguHanhCung[$SinhKhiI], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->compass, 'Sinh Khí')
        );
        $HUONGTOT[] = (object) array(
            'name' => 'Thiên Y', 'compass' => self::$ThienY[$_CUNGI], 'zodiac' => self::$QUEBATQUAI[$ThienYI], 'five_elements' => self::$arrNguHanhCung[$ThienYI], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->compass, 'Thiên Y')
        );
        $HUONGTOT[] = (object) array(
            'name' => 'Diên Niên', 'compass' => self::$DienNien[$_CUNGI], 'zodiac' => self::$QUEBATQUAI[$DienNienI], 'five_elements' => self::$arrNguHanhCung[$DienNienI], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->compass, 'Diên Niên')
        );
        $HUONGTOT[] = (object) array(
            'name' => 'Phục Vị', 'compass' => self::$PhucVi[$_CUNGI], 'zodiac' => self::$QUEBATQUAI[$PhucViI], 'five_elements' => self::$arrNguHanhCung[$PhucViI], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->compass, 'Phục Vị')
        );

        $HUONGXAU[] = (object) array(
            'name' => 'Họa Hại', 'compass' => self::$HoaHai[$_CUNGI], 'zodiac' => self::$QUEBATQUAI[$HoaHaiI], 'five_elements' => self::$arrNguHanhCung[$HoaHaiI], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->compass, 'Họa Hại')
        );
        $HUONGXAU[] = (object) array(
            'name' => 'Ngũ Quỷ', 'compass' => self::$NguQuy[$_CUNGI], 'zodiac' => self::$QUEBATQUAI[$NguQuyI], 'five_elements' => self::$arrNguHanhCung[$NguQuyI], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->compass, 'Ngũ Quỷ')
        );
        $HUONGXAU[] = (object) array(
            'name' => 'Lục Sát', 'compass' => self::$LucSat[$_CUNGI], 'zodiac' => self::$QUEBATQUAI[$LucSatI], 'five_elements' => self::$arrNguHanhCung[$LucSatI], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->compass, 'Lục Sát')
        );
        $HUONGXAU[] = (object) array(
            'name' => 'Tuyệt Mệnh', 'compass' => self::$TuyetMenh[$_CUNGI], 'zodiac' => self::$QUEBATQUAI[$TuyetMenhI], 'five_elements' => self::$arrNguHanhCung[$TuyetMenhI], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->compass, 'Tuyệt Mệnh')
        );

        $HUONGTOTXAU[] = (object) array('good' => $HUONGTOT, 'bad' => $HUONGXAU);

        $_tietKhi = $this->get_TietKhi_by_time($_daySolar, $_monthSolar, $_yearSolar, $_hour, $_minute, 0); //$this->getTietKhi($ld->jd);
        $_dayChi = $this->getDayChi($ld->jd);
        $_dayCan = $this->getDayCan($ld->jd);
        $_dayCanChi = $this->getDayCanChi($ld->jd);

        $NONGLICHX = $this->pxg_get_month_NONGLICH_TIETKHI($_daySolar, $_monthSolar, $_yearSolar, $_hour, $_minute);
        $_monthCan = $NONGLICHX->month_can;
        $_monthChi = $NONGLICHX->month_chi;
        $_monthCanChi = $NONGLICHX->month_canchi;
        $_yearCan = $NONGLICHX->year_can;
        $_yearChi = $NONGLICHX->year_chi;
        $_yearCanChi = $NONGLICHX->year_canchi;

        $_can_gio = $this->getCanHour($_daySolar, $_monthSolar, $_yearSolar, $_hour);
        $_chi_gio = $this->getChiHour($_hour);

        //THẬP THẦN
        $TANGCAN_IN_DIACHI = $this->pxg_get_TANGCAN_DIACHI($_chi_gio);
        $HOUR_THAP_THAN = [];
        for ($i = 0; $i < count($TANGCAN_IN_DIACHI); $i++) {
            array_push($HOUR_THAP_THAN, $this->pxg_get_BANGTHAPTHAN($_dayCan, $TANGCAN_IN_DIACHI[$i]));
        }
        //VÒNGTRƯỜNGSINH
        $VONGTRUONGSINH_YEAR = $this->pxg_geofesh_VONGTRUONGSINH($_yearCan, $_yearChi);
        $VONGTRUONGSINH_MONTH = $this->pxg_geofesh_VONGTRUONGSINH($_monthCan, $_monthChi);
        $VONGTRUONGSINH_DAY = $this->pxg_geofesh_VONGTRUONGSINH($_dayCan, $_dayChi);
        $VONGTRUONGSINH_HOUR = $this->pxg_geofesh_VONGTRUONGSINH($_dayCan, $_chi_gio);
        //NẠPÂM
        $NAPAM_TUTRU_YEAR = $this->pxg_geofesh_date_NAPAM($_yearCan, $_yearChi);
        $NAPAM_TUTRU_MONTH = $this->pxg_geofesh_date_NAPAM($_monthCan, $_monthChi);
        $NAPAM_TUTRU_DAY = $this->pxg_geofesh_date_NAPAM($_dayCan, $_dayChi);
        $NAPAM_TUTRU_HOUR = $this->pxg_geofesh_date_NAPAM($_can_gio, $_chi_gio);
        //THẦNSÁT_CHUYÊNSÂU
        $THANSAT_CHUYENSAU = $this->pxg_geofesh_THANSAT_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        $THANSAT_CHUYENSAU_HOUR = $THANSAT_CHUYENSAU->hour;
        $THANSAT_CHUYENSAU_DAY = $THANSAT_CHUYENSAU->day;
        $THANSAT_CHUYENSAU_MONTH = $THANSAT_CHUYENSAU->month;
        $THANSAT_CHUYENSAU_YEAR = $THANSAT_CHUYENSAU->year;

        $thoi_tru = (object)array('hour_can' => $_can_gio, 'hour_chi' => $_chi_gio, 'hour_can_chi' => $_can_gio . ' ' . $_chi_gio, 'hour_can_nguhanh' => $this->pxg_get_ngu_hanh_by_can($_can_gio), 'hour_chi_nguhanh' => $this->pxg_get_ngu_hanh_by_chi($_chi_gio), 'hour_tang_can_chi_nguhanh' => $this->pxg_get_tang_can_by_dia_chi($_chi_gio), 'hour_tang_can_chi' => $TANGCAN_IN_DIACHI, 'hour_can_thap_than' => $this->pxg_get_BANGTHAPTHAN($_dayCan, $_can_gio), 'hour_thap_than' => $HOUR_THAP_THAN, 'vongtruongsinh' => $VONGTRUONGSINH_HOUR, 'thansat' => $THANSAT_CHUYENSAU_HOUR, 'napam' => $NAPAM_TUTRU_HOUR);
        $TANGCAN_IN_DIACHI_DAY = $this->pxg_get_TANGCAN_DIACHI($_dayChi);
        $DAY_THAP_THAN = [];
        for ($i = 0; $i < count($TANGCAN_IN_DIACHI_DAY); $i++) {
            array_push($DAY_THAP_THAN, $this->pxg_get_BANGTHAPTHAN($_dayCan, $TANGCAN_IN_DIACHI_DAY[$i]));
        }
        $nhat_tru = (object)array('day' => $_daySolar, 'day_lunar' => $ld->day, 'day_can' => $_dayCan, 'day_chi' => $_dayChi, 'day_can_chi' => $_dayCanChi, 'day_can_nguhanh' => $this->pxg_get_ngu_hanh_by_can($_dayCan), 'day_chi_nguhanh' => $this->pxg_get_ngu_hanh_by_chi($_dayChi), 'day_tang_can_chi_nguhanh' => $this->pxg_get_tang_can_by_dia_chi($_dayChi), 'day_tang_can_chi' => $TANGCAN_IN_DIACHI_DAY, 'day_can_thap_than' => $this->pxg_get_BANGTHAPTHAN($_dayCan, $_dayCan), 'day_thap_than' => $DAY_THAP_THAN, 'vongtruongsinh' => $VONGTRUONGSINH_DAY, 'thansat' => $THANSAT_CHUYENSAU_DAY, 'napam' => $NAPAM_TUTRU_DAY);
        $TANGCAN_IN_DIACHI_MONTH = $this->pxg_get_TANGCAN_DIACHI($_monthChi);
        $MONTH_THAP_THAN = [];
        for ($i = 0; $i < count($TANGCAN_IN_DIACHI_MONTH); $i++) {
            array_push($MONTH_THAP_THAN, $this->pxg_get_BANGTHAPTHAN($_dayCan, $TANGCAN_IN_DIACHI_MONTH[$i]));
        }
        $nguyet_tru = (object)array('month' => $_monthSolar, 'month_lunar' => $ld->month, 'month_can' => $_monthCan, 'month_chi' => $_monthChi, 'month_can_chi' => $_monthCanChi, 'month_can_nguhanh' => $this->pxg_get_ngu_hanh_by_can($_monthCan), 'month_chi_nguhanh' => $this->pxg_get_ngu_hanh_by_chi($_monthChi), 'month_tang_can_chi_nguhanh' => $this->pxg_get_tang_can_by_dia_chi($_monthChi), 'month_tang_can_chi' => $TANGCAN_IN_DIACHI_MONTH, 'month_can_thap_than' => $this->pxg_get_BANGTHAPTHAN($_dayCan, $_monthCan), 'month_thap_than' => $MONTH_THAP_THAN, 'vongtruongsinh' => $VONGTRUONGSINH_MONTH, 'thansat' => $THANSAT_CHUYENSAU_MONTH, 'napam' => $NAPAM_TUTRU_MONTH);
        $TANGCAN_IN_DIACHI_YEAR = $this->pxg_get_TANGCAN_DIACHI($_yearChi);
        $YEAR_THAP_THAN = [];
        for ($i = 0; $i < count($TANGCAN_IN_DIACHI_YEAR); $i++) {
            array_push($YEAR_THAP_THAN, $this->pxg_get_BANGTHAPTHAN($_dayCan, $TANGCAN_IN_DIACHI_YEAR[$i]));
        }
        $NGUHANH_IN_DIACHI_YEAR = $this->pxg_get_tang_can_by_dia_chi($_yearChi); //implode(',',$this->pxg_get_tang_can_by_dia_chi($_yearChi));
        $CAN_NGUHANH_YEAR = $this->pxg_get_ngu_hanh_by_can($_yearCan);
        $CHI_NGUHANH_YEAR = $this->pxg_get_ngu_hanh_by_chi($_yearChi);

        $nien_tru = (object)array('year' => $_yearSolar, 'year_lunar' => $ld->year, 'year_can' => $_yearCan, 'year_chi' => $_yearChi, 'year_can_chi' => $_yearCanChi, 'year_can_nguhanh' => $CAN_NGUHANH_YEAR, 'year_chi_nguhanh' => $CHI_NGUHANH_YEAR, 'year_tang_can_chi_nguhanh' => $NGUHANH_IN_DIACHI_YEAR, 'year_tang_can_chi' => $TANGCAN_IN_DIACHI_YEAR, 'year_can_thap_than' => $this->pxg_get_BANGTHAPTHAN($_dayCan, $_yearCan), 'year_thap_than' => $YEAR_THAP_THAN, 'vongtruongsinh' => $VONGTRUONGSINH_YEAR, 'thansat' => $THANSAT_CHUYENSAU_YEAR, 'napam' => $NAPAM_TUTRU_YEAR);

        $SOLARDATE = (object) array('day' => $_daySolar, 'month' => $_monthSolar, 'year' => $_yearSolar);
        $NONGLICH = (object) array('day' => $ld->day, 'month' => $NONGLICHX->tietkhi, 'year' => $ld->year); //$_tietKhi
        $LUNARDATE = (object) array('day' => $ld->day, 'month' => $ld->month, 'year' => $ld->year);

        $TUTRU = (object) array('thoi_tru' => $thoi_tru, 'nhat_tru' => $nhat_tru, 'nguyet_tru' => $nguyet_tru, 'thien_tru' => $nien_tru);
        $DATE = (object) array('solar' => $SOLARDATE, 'lunar' => $LUNARDATE, 'nonglich' => $NONGLICH);
        //THẦNSÁT
        $THANSAT_REST = array(
            (object)array('name' => 'Tướng Tinh', 'day' => $this->pxg_get_chi_TUONGTINH($_dayChi), 'year' => $this->pxg_get_chi_TUONGTINH($_yearChi), 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, 'Tướng Tinh')),
            (object)array('name' => 'Quý Nhân', 'day' => $this->pxg_get_chi_QUYNHAN($_dayCan), 'year' => $this->pxg_get_chi_QUYNHAN($_yearCan), 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, 'Quý Nhân')),
            (object)array('name' => 'Văn Xương', 'day' => self::$VanXuong[$this->pxg_get_id_arr(self::$CAN, $_dayCan)], 'year' => self::$VanXuong[$this->pxg_get_id_arr(self::$CAN, $_yearCan)], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, 'Văn Xương')),
            (object)array('name' => 'Học Đường', 'day' => self::$HocDuong[$this->pxg_get_id_arr(self::$CAN, $_dayCan)], 'year' => self::$HocDuong[$this->pxg_get_id_arr(self::$CAN, $_yearCan)], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, 'Học Đường')),
            (object)array('name' => 'Đào Hoa', 'day' => $this->pxg_get_chi_DAOHOA($_dayChi), 'year' => $this->pxg_get_chi_DAOHOA($_yearChi), 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, 'Đào Hoa')),
            (object)array('name' => 'Thiên Mã', 'day' => $this->pxg_get_chi_THIENMA($_dayChi), 'year' => $this->pxg_get_chi_THIENMA($_yearChi), 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, 'Thiên Mã')),
            (object)array('name' => 'Thiên Hỷ', 'day' => self::$ThienHyMonth[$this->pxg_get_id_arr(self::$CHI, $_monthChi)], 'year' => self::$ThienHyYear[$this->pxg_get_id_arr(self::$CHI, $_yearChi)], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, 'Thiên Hỷ')),
            (object)array('name' => 'Hồng Loan', 'day' => self::$HongLoan[$this->pxg_get_id_arr(self::$CHI, $_dayChi)], 'year' => self::$HongLoan[$this->pxg_get_id_arr(self::$CHI, $_yearChi)], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, 'Hồng Loan')),
            (object)array('name' => 'Cô Thần', 'day' => $this->pxg_get_chi_COTHAN($_dayChi), 'year' => $this->pxg_get_chi_COTHAN($_yearChi), 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, 'Cô Thần')),
            (object)array('name' => 'Quả Tú', 'day' => $this->pxg_get_chi_QUATU($_dayChi), 'year' => $this->pxg_get_chi_QUATU($_yearChi), 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, 'Quả Tú')),
            (object)array('name' => 'Kiếp Sát', 'day' => self::$KiepSat[$this->pxg_get_id_arr(self::$CHI, $_dayChi)], 'year' => self::$KiepSat[$this->pxg_get_id_arr(self::$CHI, $_yearChi)], 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, 'Kiếp Sát')),
        );
        //TODO: ĐẠI VẬN
        $AGE_DAIVAN = 0;
        $CAN_DAIVAN = [];
        $CAN_THUANNGHICH = '';
        $CAN_YEAR_DAIVAN = '';
        $CHI_YEAR_DAIVAN = '';
        $TANGCAN_DAIVAN = [];
        if ($_sez == 'male') { //Nam
            if (strpos($CAN_NGUHANH_YEAR, '-') !== false) { //Khởi ngịch
                $CAN_THUANNGHICH = 'nghich';
                $AGE_DAIVAN = $this->pxg_get_date_DAIVAN($_daySolar, $_monthSolar, $_yearSolar, $CAN_THUANNGHICH);
                $CAN_YEAR_DAIVAN = $this->pxg_get_can_start_dai_van($_monthCan, $_dayCan, $CAN_THUANNGHICH);
                $CHI_YEAR_DAIVAN = $this->pxg_get_chi_start_dai_van($_monthChi, $CAN_THUANNGHICH);
            } else { //Khởi thuận
                $CAN_THUANNGHICH = 'thuan';
                $AGE_DAIVAN = $this->pxg_get_date_DAIVAN($_daySolar, $_monthSolar, $_yearSolar, $CAN_THUANNGHICH);
                $CAN_YEAR_DAIVAN = $this->pxg_get_can_start_dai_van($_monthCan, $_dayCan, $CAN_THUANNGHICH);
                $CHI_YEAR_DAIVAN = $this->pxg_get_chi_start_dai_van($_monthChi, $CAN_THUANNGHICH);
            }
        } else {/*Nữ*/
            if (strpos($CAN_NGUHANH_YEAR, '-') !== false) { //Khởi thuận
                $CAN_THUANNGHICH = 'thuan';
                $AGE_DAIVAN = $this->pxg_get_date_DAIVAN($_daySolar, $_monthSolar, $_yearSolar, $CAN_THUANNGHICH);
                $CAN_YEAR_DAIVAN = $this->pxg_get_can_start_dai_van($_monthCan, $_dayCan, $CAN_THUANNGHICH);
                $CHI_YEAR_DAIVAN = $this->pxg_get_chi_start_dai_van($_monthChi, $CAN_THUANNGHICH);
            } else { //Khởi nghịch
                $CAN_THUANNGHICH = 'nghich';
                $AGE_DAIVAN = $this->pxg_get_date_DAIVAN($_daySolar, $_monthSolar, $_yearSolar, $CAN_THUANNGHICH);
                $CAN_YEAR_DAIVAN = $this->pxg_get_can_start_dai_van($_monthCan, $_dayCan, $CAN_THUANNGHICH);
                $CHI_YEAR_DAIVAN = $this->pxg_get_chi_start_dai_van($_monthChi, $CAN_THUANNGHICH);
            }
        }
        $CHUKYDAIVAN = [];
        $CHUKYDAIVAN10 = [];
        $AGE_DAIVAN_FROM = $_yearSolar + $AGE_DAIVAN->age; //$NONGLICHX->year+$AGE_DAIVAN;//
        $YEAR_CHUKYDAIVAN = $AGE_DAIVAN_FROM + 90;
        $VONGTRUONGSINH_YEAR_DAI_VAN = [];
        $NAPAM_DAIVAN = [];
        $THANSAT_CHUYENSAU_DAIVAN = [];
        for ($_year_from = $AGE_DAIVAN_FROM; $_year_from < $YEAR_CHUKYDAIVAN; $_year_from++) {
            array_push($CHUKYDAIVAN, (object) array('year' => $_year_from, 'can_chi' => $this->getYearCanChi($_year_from)));
        }
        $idx_year_dai_van = 0;
        for ($_y = $AGE_DAIVAN_FROM; $_y < $YEAR_CHUKYDAIVAN; $_y += 10) {
            $age_year_daivan = $AGE_DAIVAN->age + ($idx_year_dai_van++ * 10);
            array_push($CHUKYDAIVAN10, (object) array('year' => $_y, 'age' => $age_year_daivan, 'can_chi' => $this->getYearCanChi($_y)));
        }
        if (self::$is_fee == true) {
            for ($_y = $AGE_DAIVAN_FROM; $_y < $YEAR_CHUKYDAIVAN; $_y += 10) {
                $cur_year_can = $this->getYearCan($_y);
                $cur_year_chi = $this->getYearChi($_y);
                array_push($VONGTRUONGSINH_YEAR_DAI_VAN, $this->pxg_geofesh_VONGTRUONGSINH($_dayCan, $cur_year_chi));
                array_push($NAPAM_DAIVAN, $this->pxg_geofesh_date_NAPAM($cur_year_can, $cur_year_chi));
                $THANSAT_EXT_CURYEAR = $this->pxg_geofesh_THANSAT_EXT($cur_year_chi, $_dayChi, $_monthChi, $_chi_gio, $cur_year_can, $_dayCan, $_monthCan, $_can_gio);
                array_push($THANSAT_CHUYENSAU_DAIVAN, $THANSAT_EXT_CURYEAR->year);
            }
        }

        for ($i = 0; $i < count($CHI_YEAR_DAIVAN); $i++) {
            array_push($TANGCAN_DAIVAN, $this->pxg_get_tang_can_dia_chi_thap_than($CHI_YEAR_DAIVAN[$i]->chi, $_dayCan));
        }
        //DAIVAN_CURYEAR
        $DAIVAN_CURYEAR = $this->pxg_geofesh_YEAR_DAIVAN($_daySolar, $_monthSolar, intval(date("Y")), $_yearSolarMain, $_hour, $_minute, self::$is_fee); //Can chi và thông tin trả về là của Đại Vận năm đó chứ không phải can chi năm đó.
        //DAIVAN_CURYEAR
        $DAIVAN = (object) array('age_start' => $AGE_DAIVAN->age, 'age_content' => $AGE_DAIVAN->content, 'thuan_nghich' => $CAN_THUANNGHICH, 'can' => $CAN_YEAR_DAIVAN, 'chi' => $CHI_YEAR_DAIVAN, 'tangcan' => $TANGCAN_DAIVAN, 'age' => $CHUKYDAIVAN10, 'circle_life' => $CHUKYDAIVAN, 'thansat' => $THANSAT_CHUYENSAU_DAIVAN, 'napam' => $NAPAM_DAIVAN, 'vongtruongsinh' => $VONGTRUONGSINH_YEAR_DAI_VAN, 'current_year' => $DAIVAN_CURYEAR);

        $DATA[] = (object) array('compass' => $HUONGTOTXAU, 'date' => $DATE, 'tutru' => $TUTRU, 'thansat' => $THANSAT_REST, 'daivan' => $DAIVAN, 'thaimenhcung' => $this->pxg_geofesh_THAICUNG_MENHCUNG($_monthCan, $_monthChi, $_hour, $_daySolar, $_monthSolar, $_yearSolar), 'is_fee' => self::$is_fee);
        $_tr .= json_encode($DATA);
        echo $_tr;
        if (isset($_POST['n'])) {
            exit();
        }
    }
    public function pxg_geofesh_THAICUNG_MENHCUNG($_monthCan, $_monthChi, $_hour, $_daySolar, $_monthSolar, $_yearSolar)
    {
        /*self::$CAN = ["Giáp","Ất","Bính","Đinh","Mậu","Kỷ","Canh","Tân","Nhâm","Quý"];*/
        $CHI_MENHCUNG = ["Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu"];
        $idx_can = $this->pxg_get_id_arr_str(self::$CAN, $_monthCan);
        $idx_chi = $this->pxg_get_id_arr_str(self::$CHI, $_monthChi);
        if (($idx_can + 1) < 10) {
            $idx_can += 1;
        } else {
            $idx_can = 0;
        }
        if (($idx_chi + 3) < 12) {
            $idx_chi += 3;
        } else {
            $idx_chi = ($idx_chi + 3) - 12;
        }
        //CHI MỆNH CUNG
        $So_Thang_Sinh = $this->pxg_get_id_arr_str($CHI_MENHCUNG, $_monthChi) + 1;
        if (($_monthSolar == 2 && $_daySolar > 14) || $_daySolar > 15) {
            $So_Thang_Sinh += 1;
        }
        $tmp_hour = (!empty($_hour) ? $_hour : 0);
        $So_Gio_Sinh = $this->pxg_get_id_arr_str($CHI_MENHCUNG, $this->pxg_get_chi_hour_MENHCUNG($tmp_hour)) + 1;
        //B1
        $A = $So_Thang_Sinh + $So_Gio_Sinh;
        //B2
        $B = (($A >= 14) ? 26 : 14) - $A;
        while ($B > 12) { //Check lại điểm này đối với trường hợp B>12
            $B = $B % 12;
        }
        $_chi = $CHI_MENHCUNG[$B - 1];
        //CAN MỆNH CUNG là CAN tháng chứa CHI mệnh CUNG;
        $_can = '';
        $_monthNo = 0;
        $_monthCanChi = '';
        for ($i = 0; $i < 12; $i++) {
            if ($this->getMonthChi($i) === $_chi) {
                $_can = $this->getMonthCan($i, $_yearSolar);
                $_monthCanChi = $this->getMonthCanChi($i, $_yearSolar);
                $_monthNo = $i;
                break;
            }
            /*$ld=$this->getLunarDate(1,$i,$_yearSolar);
         if($this->getMonthChi($ld->month)===$_chi){
            $_can=$this->getMonthCan($ld->month,$ld->year);
            $_monthCanChi=$this->getMonthCanChi($ld->month,$ld->year);
            $_monthNo=$i;
            break;
        }*/
        }
        return array('thaicung' => array('can' => self::$CAN[$idx_can], 'chi' => self::$CHI[$idx_chi]), 'chi-idx' => $idx_chi, 'menhcung' => array('can' => $_can, 'chi' => $_chi, 'month' => $_monthNo, 'can_chi' => $_monthCanChi, 'so_thang_sinh' => $So_Thang_Sinh, 'chi_thang_sinh' => $_monthChi));
    }
    public function pxg_get_chi_hour_MENHCUNG($_hour)
    {
        if ($_hour >= 1 && $_hour < 3) {
            return "Sửu";
        } else if ($_hour >= 3 && $_hour < 5) {
            return "Dần";
        } else if ($_hour >= 5 && $_hour < 7) {
            return "Mão";
        } else if ($_hour >= 7 && $_hour < 9) {
            return "Thìn";
        } else if ($_hour >= 9 && $_hour < 11) {
            return "Tỵ";
        } else if ($_hour >= 11 && $_hour < 13) {
            return "Ngọ";
        } else if ($_hour >= 13 && $_hour < 15) {
            return "Mùi";
        } else if ($_hour >= 15 && $_hour < 17) {
            return "Thân";
        } else if ($_hour >= 17 && $_hour < 19) {
            return "Dậu";
        } else if ($_hour >= 19 && $_hour < 21) {
            return "Tuất";
        } else if ($_hour >= 21 && $_hour < 23) {
            return "Hợi";
        } else {
            return "Tý";
        }
        return '';
    }
    public function pxg_geofesh_THANSAT_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $THANSAT_CHUYENSAU_HOUR = [];
        $THANSAT_CHUYENSAU_DAY = [];
        $THANSAT_CHUYENSAU_MONTH = [];
        $THANSAT_CHUYENSAU_YEAR = [];
        //Đưa tìm chi Hoa Cái từ chi ngày hoặc năm. Sau đó nếu Chi của tứ trụ nào trùng thì có Hoa Cái ở đó.
        $_hoaCai = $this->pxg_geofesh_HOACAI_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio);
        if (!empty($_hoaCai)) {
            foreach ($_hoaCai as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        //var_dump($_hoaCai);
        $_daoHoa = $this->pxg_geofesh_DAOHOA_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio);
        if (!empty($_daoHoa)) {
            foreach ($_daoHoa as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        //echo json_encode($_daoHoa);
        $_dichMa = $this->pxg_geofesh_DICHMA_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio);
        if (!empty($_dichMa)) {
            foreach ($_dichMa as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        //echo json_encode($_dichMa);
        $_vanXuong = $this->pxg_geofesh_VANXUONG_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_vanXuong)) {
            foreach ($_vanXuong as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        //echo json_encode($_vanXuong);
        $_quyNhan = $this->pxg_geofesh_QUYNHAN_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_quyNhan)) {
            foreach ($_quyNhan as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_duongNhan = $this->pxg_geofesh_DUONGNHAN_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_dayCan);
        if (!empty($_duongNhan)) {
            foreach ($_duongNhan as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        //echo json_encode($_duongNhan);
        $_tamSat = $this->pxg_geofesh_TAMSAT_EXT($_yearChi, $_dayChi, $_monthChi);
        if (!empty($_tamSat)) {
            foreach ($_tamSat as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_tuongTinh = $this->pxg_geofesh_TUONGTINH_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio);
        if (!empty($_tuongTinh)) {
            foreach ($_tuongTinh as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_huyetNhan = $this->pxg_geofesh_HUYETNHAN_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio);
        if (!empty($_huyetNhan)) {
            foreach ($_huyetNhan as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_kiepSat = $this->pxg_geofesh_KIEPSAT_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio);
        if (!empty($_kiepSat)) {
            foreach ($_kiepSat as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_kimQuy = $this->pxg_geofesh_KIMQUY_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_kimQuy)) {
            foreach ($_kimQuy as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_vongThan = $this->pxg_geofesh_VONGTHAN_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio);
        if (!empty($_vongThan)) {
            foreach ($_vongThan as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_coThan = $this->pxg_geofesh_COTHAN_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio);
        if (!empty($_coThan)) {
            foreach ($_coThan as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_thienDuc = $this->pxg_geofesh_THIENDUC_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_thienDuc)) {
            foreach ($_thienDuc as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_nguyetDuc = $this->pxg_geofesh_NGUYETDUC_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_nguyetDuc)) {
            foreach ($_nguyetDuc as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_tangMon = $this->pxg_geofesh_TANGMON_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_tangMon)) {
            foreach ($_tangMon as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_dieuKhach = $this->pxg_geofesh_DIEUKHACH_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_dieuKhach)) {
            foreach ($_dieuKhach as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_lucTu = $this->pxg_geofesh_LUCTU_EXT($_dayCanChi);
        if (!empty($_lucTu)) {
            foreach ($_lucTu as $key => $_obj) {
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
            }
        }
        $_hongDiem = $this->pxg_geofesh_HONGDIEM_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_hongDiem)) {
            foreach ($_hongDiem as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_luuHa = $this->pxg_geofesh_LUUHA_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_luuHa)) {
            foreach ($_luuHa as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_thienHy = $this->pxg_geofesh_THIENHY_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_thienHy)) {
            foreach ($_thienHy as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        //TEST
        /*$_chi_gio='Tý';
    $_dayChi='Thìn';
    $_monthChi='Tỵ';
    $_yearChi='Tỵ';

    $_can_gio='Thân';
    $_dayCan='Bính';
    $_monthCan='Quý';
    $_yearCan='Nhâm';
    $_dayCanChi='Mậu Tý';*/
        //TEST

        $_thapCanLoc = $this->pxg_geofesh_THAPCANLOC_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_thapCanLoc)) {
            foreach ($_thapCanLoc as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_thienNo = $this->pxg_geofesh_THIENNO_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_thienNo)) {
            foreach ($_thienNo as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_quyHop = $this->pxg_geofesh_QUYHOP_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_quyHop)) {
            foreach ($_quyHop as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_quyThuc = $this->pxg_geofesh_QUYTHUC_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_quyThuc)) {
            foreach ($_quyThuc as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_lucQuyHop = $this->pxg_geofesh_LUCQUYHOP_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_lucQuyHop)) {
            foreach ($_lucQuyHop as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_thienLa = $this->pxg_geofesh_THIENLA_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_thienLa)) {
            foreach ($_thienLa as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_diaVong = $this->pxg_geofesh_DIAVONG_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
        if (!empty($_diaVong)) {
            foreach ($_diaVong as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        $_phiKhongKhoiCuong = $this->pxg_geofesh_KHOICUONG_EXT($_dayChi, $_dayCan);
        if (!empty($_phiKhongKhoiCuong)) {
            foreach ($_phiKhongKhoiCuong as $key => $_obj) {
                if (isset($_obj->hour)) {
                    array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                }
                if (isset($_obj->day)) {
                    array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                }
                if (isset($_obj->month)) {
                    array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                }
                if (isset($_obj->year)) {
                    array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                }
            }
        }
        //TODOKHONGVONG => chỉ xét nếu đủ giờ, ngày, tháng, năm (theo yêu cầu);
        if (!empty($_can_gio) && !empty($_chi_gio) && !empty($_dayCan) && !empty($_dayChi) && !empty($_monthCan) && !empty($_monthChi) && !empty($_yearCan) && !empty($_yearChi)) {
            $_khongVong = $this->pxg_geofesh_KHONGVONG_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio);
            if (!empty($_khongVong)) {
                foreach ($_khongVong as $key => $_obj) {
                    //$_obj->name=$_obj->name.' '.$_can_gio.' '.$_chi_gio;
                    if (isset($_obj->hour)) {
                        array_push($THANSAT_CHUYENSAU_HOUR, $_obj);
                    }
                    if (isset($_obj->day)) {
                        array_push($THANSAT_CHUYENSAU_DAY, $_obj);
                    }
                    if (isset($_obj->month)) {
                        array_push($THANSAT_CHUYENSAU_MONTH, $_obj);
                    }
                    if (isset($_obj->year)) {
                        array_push($THANSAT_CHUYENSAU_YEAR, $_obj);
                    }
                }
            }
        }
        return json_decode(json_encode(array('hour' => $THANSAT_CHUYENSAU_HOUR, 'day' => $THANSAT_CHUYENSAU_DAY, 'month' => $THANSAT_CHUYENSAU_MONTH, 'year' => $THANSAT_CHUYENSAU_YEAR)));
    }
    public function pxg_geofesh_KHONGVONG_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $_name = 'Không Vong';
        $_TOA = [];
        $idx = $this->pxg_get_id_arr2_str(self::$KHONGVONG_LUCTHAP_HOAGIAP, $_dayCan . ' ' . $_dayChi); //pxg_get_id_arr2_str
        //Xác định Không vong từ CAN CHI trụ ngày => Các trụ chứa địa chi Không Vong ngày => chứa Thần Sát Không Vong.
        if ($idx != -1) { //Có địa CHI Không Vong;
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            $CHI_KV = self::$KHONGVONG[$idx]; //self::$KHONGVONG=["Tuất Hợi","Thân Dậu","Ngọ Mùi","Thìn Tỵ","Dần Mão","Tý Sửu"];
            /* Em check lại rồi bác nhé. Đoạn code tính ngày kia em quên note lại. Ngày Không Vong là Địa Chi của 1 ngày tác động lên trụ: Năm, Tháng, Giờ. Tức là không phải ghi chú ở trụ Ngày.
            Tức là câu lệnh này cần note lại vì không có Không Vong ở trụ ngày mà dựa trên Địa Chi của ngày hôm đó lấy ra Tuần Giáp tương ứng => trả về Địa Chi xác định Không Vong. Sau đó lấy Địa Chi trụ: giờ, tháng, năm kiểm tra nếu trụ nào có xuất hiện trong Địa Chi Không Vong vừa xác định thì là Không Vong.
            //array_push($_TOA,array('name'=>$_name,'day'=>$_dayChi,'toa'=>$CHI_KV,'day_can_chu'=>$_dayCan,'info'=>$_data_info));
            */
            if (!empty($_yearChi) && strripos($CHI_KV, $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $CHI_KV, 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (!empty($_monthChi) && strripos($CHI_KV, $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $CHI_KV, 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }

            if (!empty($_chi_gio) && strripos($CHI_KV, $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $CHI_KV, 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    //TODO: THẦN SÁT CHUYÊN SÂU
    public function pxg_geofesh_KHOICUONG_EXT($_dayChi, $_dayCan)
    {
        $ARR_TOA = ['Canh Thìn', 'Canh Tuất', 'Mậu Tuất', 'Mậu Thìn'];
        $idx = $this->pxg_get_id_arr_str($ARR_TOA, $_dayCan . ' ' . $_dayChi);
        $_TOA = [];
        if ($idx != -1) {
            $_name = 'Khôi Cương';
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_THIENLA_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $ARR = ['Tuất', 'Hợi'];
        $ARR_TOA = ['Hợi', 'Tuất'];
        $idx = $this->pxg_get_id_arr_str($ARR, $_yearChi);
        $_TOA = [];
        $_name = 'Thiên La';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if ($ARR_TOA[$idx] == $_yearChi) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_dayChi) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_monthChi) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_chi_gio) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_DIAVONG_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $ARR = ['Thìn', 'Tỵ'];
        $ARR_TOA = ['Tỵ', 'Thìn'];
        $idx = $this->pxg_get_id_arr_str($ARR, $_yearChi);
        $_TOA = [];
        $_name = 'Địa Võng';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if ($ARR_TOA[$idx] == $_yearChi) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_dayChi) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_monthChi) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_chi_gio) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_LUCQUYHOP_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $ARR_TOA = ['Giáp Tý - Giáp Ngọ', 'Ất Sửu - Ất Tỵ', 'Bính Dần - Bính Thìn', '', 'Mậu Tý - Mậu Ngọ', 'Kỷ Sửu - Kỷ Tỵ', 'Canh Tý - Canh Ngọ', 'Tân Hợi - Tân Mùi', 'Nhâm Thân - Nhâm Tuất'];
        $idx = $this->pxg_get_id_arr_str(self::$CAN, $_dayCan);
        $_TOA = [];
        $_name = 'Lục Quý Hợp';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idx],  $_yearCan . ' ' . $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_dayCan . ' ' . $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_monthCan . ' ' . $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_can_gio . ' ' . $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_QUYTHUC_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $ARR_TOA = ['Bính Thìn - Bính Dần', 'Đinh Hợi - Đinh Dậu', 'Mậu Tý - Mậu Ngọ', 'Kỷ Tỵ - Kỷ Sửu', 'Canh Tý - Canh Ngọ', 'Tân Hợi - Tân Mùi', 'Nhâm Tuất - Nhâm Thân', 'Quý Tỵ - Quý Mão', 'Giáp Tý - Giáp Ngọ', 'Ất Sửu - Ất Tỵ'];
        $idx = $this->pxg_get_id_arr_str(self::$CAN, $_dayCan);
        $_TOA = [];
        $_name = 'Quý Thực';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idx],  $_yearCan . ' ' . $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_dayCan . ' ' . $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_monthCan . ' ' . $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_can_gio . ' ' . $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_QUYHOP_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $ARR_TOA = ['Kỷ Sửu - Kỷ Mùi', 'Canh Tý - Canh Thân', 'Tân Dậu - Tân Hợi', '', 'Quý Sửu - Quý Mùi', 'Giáp Tý - Giáp Thân', 'Ất Sửu - Ất Mùi', 'Bính Ngọ - Bính Dần', 'Đinh Mão - Đinh Tỵ', ''];
        $idx = $this->pxg_get_id_arr_str(self::$CAN, $_dayCan);
        $_TOA = [];
        $_name = 'Quý Hợp';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idx],  $_yearCan . ' ' . $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_dayCan . ' ' . $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_monthCan . ' ' . $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_can_gio . ' ' . $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_THIENNO_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $ARR_TOA = ['Giáp Tý', 'Giáp Tý', 'Mậu Dần', 'Mậu Dần', 'Giáp Ngọ', 'Giáp Ngọ', 'Giáp Ngọ', 'Mậu Thân', 'Mậu Thân', 'Mậu Thân', 'Giáp Tý'];
        $idx = $this->pxg_get_id_arr_str(self::$CHI, $_dayChi);
        $_TOA = [];
        $_name = 'Thiên Nộ';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idx],  $_yearCan . ' ' . $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_dayCan . ' ' . $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_monthCan . ' ' . $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_can_gio . ' ' . $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_THAPCANLOC_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $ARR_TOA = ['Dần', 'Mão', 'Tỵ', 'Ngọ', 'Tỵ', 'Ngọ', 'Thân', 'Dậu', 'Hợi', 'Tý'];
        $idx = $this->pxg_get_id_arr_str(self::$CAN, $_dayCan);
        $_TOA = [];
        $_name = 'Thập Can Lộc';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idx], $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_THIENHY_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        /*Tính theo Năm hoặc Tháng + Chi: ngày tháng năm*/
        $ARR_TOA_YEAR = ['Dậu', 'Thân', 'Mùi', 'Ngọ', 'Tỵ', 'Thìn', 'Mão', 'Dần', 'Sửu', 'Tý', 'Hợi', 'Tuất'];
        $idx = $this->pxg_get_id_arr_str(self::$CHI, $_yearChi);
        $_TOA = [];
        $_name = 'Thiên Hỷ';/*check lại tên Lưu Hà*/
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA_YEAR[$idx], $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA_YEAR[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA_YEAR[$idx], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA_YEAR[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA_YEAR[$idx], $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA_YEAR[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA_YEAR[$idx], $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA_YEAR[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_LUUHA_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        /*Sao Lưu Hạ => Tính từ Can Ngày + Chi tứ trụ để xét vị trí ứng không?*/
        $ARR_TOA = ['Dậu', 'Tuất', 'Mùi', 'Thân', 'Tỵ', 'Ngọ', 'Thìn', 'Mão', 'Dần', 'Hợi'];
        $idx = $this->pxg_get_id_arr_str(self::$CAN, $_dayCan);
        $_TOA = [];
        $_name = 'Lưu Hạ';/*check lại tên Lưu Hà*/
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idx], $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_HONGDIEM_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        /*Sao Hồng Diễm Sát => Tính từ Can Ngày + Chi tứ trụ để xét vị trí ứng không?*/
        $ARR_TOA = ["Thân Ngọ", "Thân Ngọ", "Dần", "Mùi", "Thìn", "Thìn", "Tuất", "Dậu", "Tý", "Thân"];
        $idx = $this->pxg_get_id_arr_str(self::$CAN, $_dayCan);
        $_TOA = [];
        $_name = 'Hồng Diễm';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idx], $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_KIMQUY_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $ARR_TOA = ["Thìn", "Tỵ", "Mùi", "Thân", "Mùi", "Thân", "Tuất", "Hợi", "Sửu", "Dần"];
        $idx = $this->pxg_get_id_arr_str(self::$CAN, $_yearCan);
        $idxDAY = $this->pxg_get_id_arr_str(self::$CAN, $_dayCan);
        $_TOA = [];
        $_name = 'Kim Quỹ';
        /*if($idx!=-1){
        if(strripos($ARR_TOA[$idx], $_yearChi)!==false){
            array_push($_TOA,array('name'=>$_name,'year'=>$_yearChi,'toa'=>$ARR_TOA[$idx],'year_can_chu'=>$_yearCan));
        }
        if(strripos($ARR_TOA[$idx], $_monthChi)!==false){
            array_push($_TOA,array('name'=>$_name,'month'=>$_monthChi,'toa'=>$ARR_TOA[$idx],'year_can_chu'=>$_yearCan));
        }
        if(strripos($ARR_TOA[$idx], $_dayChi)!==false){
            array_push($_TOA,array('name'=>$_name,'day'=>$_dayChi,'toa'=>$ARR_TOA[$idx],'year_can_chu'=>$_yearCan));
        }
        if(strripos($ARR_TOA[$idx], $_chi_gio)!==false){
            array_push($_TOA,array('name'=>$_name,'hour'=>$_chi_gio,'toa'=>$ARR_TOA[$idx],'year_can_chu'=>$_yearCan));
        }
    }*/
        if ($idxDAY != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idxDAY], $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idxDAY], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idxDAY], $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idxDAY], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idxDAY], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idxDAY], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idxDAY], $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idxDAY], 'day_can_chu' => $_dayCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_KIEPSAT_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio)
    {
        $ARR_TOA = ["Tỵ", "Dần", "Hợi", "Thân", "Tỵ", "Dần", "Hợi", "Thân", "Tỵ", "Dần", "Hợi", "Thân"];
        $idx = $this->pxg_get_id_arr_str(self::$CHI, $_yearChi);
        $idxDAY = $this->pxg_get_id_arr_str(self::$CHI, $_dayChi);
        $_TOA = [];
        $_name = 'Kiếp Sát';
        /*if($idx!=-1){
        if(strripos($ARR_TOA[$idx], $_yearChi)!==false){
            array_push($_TOA,array('name'=>$_name,'year'=>$_yearChi,'toa'=>$ARR_TOA[$idx],'year_chi_chu'=>$_yearChi));
        }
        if(strripos($ARR_TOA[$idx], $_monthChi)!==false){
            array_push($_TOA,array('name'=>$_name,'month'=>$_monthChi,'toa'=>$ARR_TOA[$idx],'year_chi_chu'=>$_yearChi));
        }
        if(strripos($ARR_TOA[$idx], $_dayChi)!==false){
            array_push($_TOA,array('name'=>$_name,'day'=>$_dayChi,'toa'=>$ARR_TOA[$idx],'year_chi_chu'=>$_yearChi));
        }
        if(strripos($ARR_TOA[$idx], $_chi_gio)!==false){
            array_push($_TOA,array('name'=>$_name,'hour'=>$_chi_gio,'toa'=>$ARR_TOA[$idx],'year_chi_chu'=>$_yearChi));
        }
    }*/
        if ($idxDAY != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idxDAY], $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idxDAY], 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idxDAY], $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idxDAY], 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idxDAY], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idxDAY], 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idxDAY], $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idxDAY], 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_HUYETNHAN_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio)
    {
        $ARR_TOA = ["Tuất", "Dậu", "Thân", "Mùi", "Ngọ", "Tỵ", "Thìn", "Mão", "Dần", "Sửu", "Tý", "Hợi"];
        $idx = $this->pxg_get_id_arr_str(self::$CHI, $_yearChi);
        $idxDAY = $this->pxg_get_id_arr_str(self::$CHI, $_dayChi);
        $_TOA = [];
        $_name = 'Huyết Nhẫn';
        /*if($idx!=-1){
        if(strripos($ARR_TOA[$idx], $_yearChi)!==false){
            array_push($_TOA,array('name'=>$_name,'year'=>$_yearChi,'toa'=>$ARR_TOA[$idx],'year_chi_chu'=>$_yearChi));
        }
        if(strripos($ARR_TOA[$idx], $_monthChi)!==false){
            array_push($_TOA,array('name'=>$_name,'month'=>$_monthChi,'toa'=>$ARR_TOA[$idx],'year_chi_chu'=>$_yearChi));
        }
        if(strripos($ARR_TOA[$idx], $_dayChi)!==false){
            array_push($_TOA,array('name'=>$_name,'day'=>$_dayChi,'toa'=>$ARR_TOA[$idx],'year_chi_chu'=>$_yearChi));
        }
        if(strripos($ARR_TOA[$idx], $_chi_gio)!==false){
            array_push($_TOA,array('name'=>$_name,'hour'=>$_chi_gio,'toa'=>$ARR_TOA[$idx],'year_chi_chu'=>$_yearChi));
        }
    }*/
        if ($idxDAY != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idxDAY], $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idxDAY], 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idxDAY], $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idxDAY], 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idxDAY], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idxDAY], 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idxDAY], $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idxDAY], 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_TUONGTINH_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio)
    {
        $ARR_TOA = ["Tý", "Dậu", "Ngọ", "Mão", "Tý", "Dậu", "Ngọ", "Mão", "Tý", "Dậu", "Ngọ", "Mão"];
        $idx = $this->pxg_get_id_arr_str(self::$CHI, $_yearChi);
        $idxDAY = $this->pxg_get_id_arr_str(self::$CHI, $_dayChi);
        $_TOA = [];
        $_name = 'Tướng Tinh';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idx], $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
        }
        /*if($idxDAY!=-1){
        if(strripos($ARR_TOA[$idxDAY], $_yearChi)!==false){
            array_push($_TOA,array('name'=>$_name,'year'=>$_yearChi,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if(strripos($ARR_TOA[$idxDAY], $_monthChi)!==false){
            array_push($_TOA,array('name'=>$_name,'month'=>$_monthChi,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if(strripos($ARR_TOA[$idxDAY], $_dayChi)!==false){
            array_push($_TOA,array('name'=>$_name,'day'=>$_dayChi,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if(strripos($ARR_TOA[$idxDAY], $_chi_gio)!==false){
            array_push($_TOA,array('name'=>$_name,'hour'=>$_chi_gio,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
    }*/
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_THIENDUC_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        /*Cách xác định: lấy Chi tháng sinh làm mốc đối chiếu với Can hay Chi của năm, tháng, ngày, giờ. Ví dụ: tháng Giêng Thiên đức ở Đinh, tháng 2 ở Thân,... tháng Chạp ở Canh.*/
        $ARR_TOA = ["Tỵ"/*Chi*/, "Canh", "Đinh", "Thân"/*Chi*/, "Nhâm", "Tân", "Hợi"/*Chi*/, "Giáp", "Quý", "Dần"/*Chi*/, "Bính", "Ất"];
        $idxMONTH = $this->pxg_get_id_arr_str(self::$CHI, $_monthChi);
        $_TOA = [];
        $_name = 'Thiên Đức';
        if ($idxMONTH != -1) {
            $_tmp = $ARR_TOA[$idxMONTH];/*Xác định giá trị này là CAN hay CHI*/
            //var_dump($_tmp);
            $idx = $this->pxg_get_id_arr_str(self::$CHI, $_tmp);
            if ($idx != -1) { //Tọa tại Chi trong bảng $ARR_TOA; Tính theo CHI
                $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
                if (strripos($_tmp, $_yearChi) !== false) {
                    array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $_tmp, 'month_chi_chu' => $_monthChi, 'info' => $_data_info));
                }
                if (strripos($_tmp, $_monthChi) !== false) {
                    array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $_tmp, 'month_chi_chu' => $_monthChi, 'info' => $_data_info));
                }
                if (strripos($_tmp, $_dayChi) !== false) {
                    array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $_tmp, 'month_chi_chu' => $_monthChi, 'info' => $_data_info));
                }
                if (strripos($_tmp, $_chi_gio) !== false) {
                    array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $_tmp, 'month_chi_chu' => $_monthChi, 'info' => $_data_info));
                }
            }
            /*else{//Tính theo CAN
             $idx=$this->pxg_get_id_arr_str(self::$CAN,$_tmp);
            if(strripos($_tmp, $_yearCan)!==false){
                array_push($_TOA,array('name'=>$_name,'year'=>$_yearChi,'toa'=>$_tmp,'month_can_chu'=>$_monthChi));
            }
            if(strripos($_tmp, $_monthCan)!==false){
                array_push($_TOA,array('name'=>$_name,'month'=>$_monthChi,'toa'=>$_tmp,'month_can_chu'=>$_monthChi));
            }
            if(strripos($_tmp, $_dayCan)!==false){
                array_push($_TOA,array('name'=>$_name,'day'=>$_dayChi,'toa'=>$_tmp,'month_can_chu'=>$_monthChi));
            }
            if(strripos($_tmp, $_can_gio)!==false){
                array_push($_TOA,array('name'=>$_name,'hour'=>$_chi_gio,'toa'=>$_tmp,'month_can_chu'=>$_monthChi));
            }
        }*/
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_NGUYETDUC_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $ARR_TOA = ["Nhâm", "Giáp", "Bính", "Canh"];
        $ARR_CHI = ["Thân Tý Thìn", "Hợi Mão Mùi", "Dần Ngọ Tuất", "Tỵ Dậu Sửu"];
        $idx = $this->pxg_get_id_arr_str($ARR_CHI, $_monthChi);
        $_TOA = [];
        $_name = 'Nguyệt Đức';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if ($ARR_TOA[$idx] == $_yearCan) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearCan, 'toa' => $ARR_TOA[$idx], 'month_chi_chu' => $_monthChi, 'info' => $_data_info));
            }
            /*if($ARR_TOA[$idx]==$_monthCan){
            array_push($_TOA,array('name'=>$_name,'month'=>$_monthCan,'toa'=>$ARR_TOA[$idx],'month_chi_chu'=>$_monthChi,'info'=>$_data_info));
        }*/
            /*if($ARR_TOA[$idx]==$_dayCan){
            array_push($_TOA,array('name'=>$_name,'day'=>$_dayCan,'toa'=>$ARR_TOA[$idx],'month_chi_chu'=>$_monthChi,'info'=>$_data_info));
        }*/
            /*if($ARR_TOA[$idx]==$_can_gio){
            array_push($_TOA,array('name'=>$_name,'hour'=>$_can_gio,'toa'=>$ARR_TOA[$idx],'month_chi_chu'=>$_monthChi,'info'=>$_data_info));
        }*/
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_TAMSAT_EXT($_yearChi, $_dayChi, $_monthChi)
    {
        //Note: vào các năm hoặc tháng Thân Tý Thìn mà gặp ngày Tỵ Ngọ Mùi là phạm Tam Sát. Tam Sát chỉ áp dụng cho Ngày
        $ARR_TOA = ["Tỵ Ngọ Mùi", "Thân Dậu Tuất", "Hợi Tý Sửu", "Dần Mão Thìn"];
        $ARR_CHI = ["Thân Tý Thìn", "Hợi Mão Mùi", "Dần Ngọ Tuất", "Tỵ Dậu Sửu"];
        $idx = $this->pxg_get_id_arr_str($ARR_CHI, $_yearChi);
        $idxMONTH = $this->pxg_get_id_arr_str($ARR_CHI, $_monthChi);
        $_TOA = [];
        $_name = 'Tam Sát';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idx], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
        }
        if ($idxMONTH != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idxMONTH], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idxMONTH], 'month_chi_chu' => $_monthChi, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_DUONGNHAN_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_dayCan)
    {
        $ARR_TOA = ["Mão", "Ngọ", "Ngọ", "Dậu", "Tý"];
        $ARR_CAN = ["Giáp", "Bính", "Mậu", "Canh", "Nhâm"];
        $idx = $this->pxg_get_id_arr_str($ARR_CAN, $_dayCan);
        $_TOA = [];
        $_name = 'Dương Nhẫn';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if ($ARR_TOA[$idx] == $_yearChi) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_monthChi) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_dayChi) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_chi_gio) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_QUYNHAN_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $ARR_TOA = ["Sửu Mùi", "Tý Thân", "Hợi Dậu", "Mão Tỵ", "Ngọ Dần"];
        $ARR_CAN = ["Giáp Mậu Canh", "Ất Kỷ", "Bính Đinh", "Nhâm Quý", "Tân"];
        $idx = $this->pxg_get_id_arr_str($ARR_CAN, $_yearCan);
        $idxDAY = $this->pxg_get_id_arr_str($ARR_CAN, $_dayCan);
        $_TOA = [];
        $_name = 'Quý Nhân';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idx], $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
        }
        /*if($idxDAY!=-1){
        if(strripos($ARR_TOA[$idxDAY], $_yearChi)!==false){
            array_push($_TOA,array('name'=>$_name,'year'=>$_yearChi,'toa'=>$ARR_TOA[$idxDAY],'day_can_chu'=>$_dayCan));
        }
        if(strripos($ARR_TOA[$idxDAY], $_monthChi)!==false){
            array_push($_TOA,array('name'=>$_name,'month'=>$_monthChi,'toa'=>$ARR_TOA[$idxDAY],'day_can_chu'=>$_dayCan));
        }
        if(strripos($ARR_TOA[$idxDAY], $_dayChi)!==false){
            array_push($_TOA,array('name'=>$_name,'day'=>$_dayChi,'toa'=>$ARR_TOA[$idxDAY],'day_can_chu'=>$_dayCan));
        }
        if(strripos($ARR_TOA[$idxDAY], $_chi_gio)!==false){
            array_push($_TOA,array('name'=>$_name,'hour'=>$_chi_gio,'toa'=>$ARR_TOA[$idxDAY],'day_can_chu'=>$_dayCan));
        }
    }*/
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_VANXUONG_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio, $_yearCan, $_dayCan, $_monthCan, $_can_gio)
    {
        $ARR_TOA = ["Tỵ", "Ngọ", "Thân", "Dậu", "Hợi", "Tý", "Dần", "Mão"];
        $ARR_CAN = ["Giáp", "Ất", "Bính Dậu", "Đinh Kỷ", "Canh", "Tân", "Nhâm", "Quý"];
        $idx = $this->pxg_get_id_arr_str($ARR_CAN, $_yearCan);
        $idxDAY = $this->pxg_get_id_arr_str($ARR_CAN, $_dayCan);
        $_TOA = [];
        $_name = 'Văn Xương';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if ($ARR_TOA[$idx] == $_yearChi) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_monthChi) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_dayChi) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_chi_gio) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'year_can_chu' => $_yearCan, 'info' => $_data_info));
            }
        }
        /*if($idxDAY!=-1){
        if($ARR_TOA[$idxDAY]==$_yearChi){
            array_push($_TOA,array('name'=>$_name,'year'=>$_yearChi,'toa'=>$ARR_TOA[$idxDAY],'day_can_chu'=>$_dayCan));
        }
        if($ARR_TOA[$idxDAY]==$_monthChi){
            array_push($_TOA,array('name'=>$_name,'month'=>$_monthChi,'toa'=>$ARR_TOA[$idxDAY],'day_can_chu'=>$_dayCan));
        }
        if($ARR_TOA[$idxDAY]==$_dayChi){
            array_push($_TOA,array('name'=>$_name,'day'=>$_dayChi,'toa'=>$ARR_TOA[$idxDAY],'day_can_chu'=>$_dayCan));
        }
        if($ARR_TOA[$idxDAY]==$_chi_gio){
            array_push($_TOA,array('name'=>$_name,'hour'=>$_chi_gio,'toa'=>$ARR_TOA[$idxDAY],'day_can_chu'=>$_dayCan));
        }
    }*/
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_VONGTHAN_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio)
    {
        $ARR_TOA = ["Hợi", "Dần", "Tỵ", "Thân"];
        $ARR_CHI = ["Thân Tý Thìn", "Hợi Mão Mùi", "Dần Ngọ Tuất", "Tỵ Dậu Sửu"];
        $idx = $this->pxg_get_id_arr_str($ARR_CHI, $_yearChi);
        $_TOA = [];
        $_name = 'Vong Thần';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if ($ARR_TOA[$idx] == $_yearChi) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_dayChi) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_monthChi) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_chi_gio) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_COTHAN_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio)
    {
        $ARR_TOA = ["Sửu Tỵ", "Thìn Thân", "Mùi Hợi", "Tuất Dần"];
        $ARR_CHI = ["Thân Tý Thìn", "Hợi Mão Mùi", "Dần Ngọ Tuất", "Tỵ Dậu Sửu"];
        $idx = $this->pxg_get_id_arr_str($ARR_CHI, $_yearChi);
        $_TOA = [];
        $_name = 'Cô Thần';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if (strripos($ARR_TOA[$idx], $_yearChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_dayChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_monthChi) !== false) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if (strripos($ARR_TOA[$idx], $_chi_gio) !== false) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_HOACAI_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio)
    {
        $HOACAI = ["Thìn", "Mùi", "Tuất", "Sửu"];
        $HOACAI_CHI = ["Thân Tý Thìn", "Hợi Mão Mùi", "Dần Ngọ Tuất", "Tỵ Dậu Sửu"];
        $idx = $this->pxg_get_id_arr_str($HOACAI_CHI, $_yearChi);
        $idxDAY = $this->pxg_get_id_arr_str($HOACAI_CHI, $_dayChi);
        $HOACAI_TOA = [];
        $_name = 'Hoa Cái';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if ($HOACAI[$idx] == $_yearChi) {
                array_push($HOACAI_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $HOACAI[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($HOACAI[$idx] == $_dayChi) {
                array_push($HOACAI_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $HOACAI[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($HOACAI[$idx] == $_monthChi) {
                array_push($HOACAI_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $HOACAI[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($HOACAI[$idx] == $_chi_gio) {
                array_push($HOACAI_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $HOACAI[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
        }
        /*if($idxDAY!=-1){
        if($HOACAI[$idxDAY]==$_yearChi){
            array_push($HOACAI_TOA,array('name'=>$_name,'year'=>$_yearChi,'toa'=>$HOACAI[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if($HOACAI[$idxDAY]==$_dayChi){
            array_push($HOACAI_TOA,array('name'=>$_name,'day'=>$_dayChi,'toa'=>$HOACAI[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if($HOACAI[$idxDAY]==$_monthChi){
            array_push($HOACAI_TOA,array('name'=>$_name,'month'=>$_monthChi,'toa'=>$HOACAI[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if($HOACAI[$idxDAY]==$_chi_gio){
            array_push($HOACAI_TOA, array('name'=>$_name,'hour'=>$_chi_gio,'toa'=>$HOACAI[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
    }*/
        return (count($HOACAI_TOA) != 0) ? json_decode(json_encode($HOACAI_TOA)) : '';
    }
    public function pxg_geofesh_DAOHOA_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio)
    {
        $ARR_TOA = ["Dậu", "Tý", "Mão", "Ngọ"];
        $ARR_CHI = ["Thân Tý Thìn", "Hợi Mão Mùi", "Dần Ngọ Tuất", "Tỵ Dậu Sửu"];
        $idx = $this->pxg_get_id_arr_str($ARR_CHI, $_yearChi);
        $idxDAY = $this->pxg_get_id_arr_str($ARR_CHI, $_dayChi);
        $_TOA = [];
        $_name = 'Đào Hoa';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if ($ARR_TOA[$idx] == $_yearChi) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_dayChi) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_monthChi) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_chi_gio) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
        }
        /*if($idxDAY!=-1){
        if($ARR_TOA[$idxDAY]==$_yearChi){
            array_push($_TOA,array('name'=>$_name,'year'=>$_yearChi,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if($ARR_TOA[$idxDAY]==$_dayChi){
            array_push($_TOA,array('name'=>$_name,'day'=>$_dayChi,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if($ARR_TOA[$idxDAY]==$_monthChi){
            array_push($_TOA,array('name'=>$_name,'month'=>$_monthChi,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if($ARR_TOA[$idxDAY]==$_chi_gio){
            array_push($_TOA, array('name'=>$_name,'hour'=>$_chi_gio,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
    }*/
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_DICHMA_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio)
    {
        //Trạch Mã, Thiên Mã là tên gọi khác.
        $ARR_TOA = ["Dần", "Tỵ", "Thân", "Hợi"];
        $ARR_CHI = ["Thân Tý Thìn", "Hợi Mão Mùi", "Dần Ngọ Tuất", "Tỵ Dậu Sửu"];
        $idx = $this->pxg_get_id_arr_str($ARR_CHI, $_yearChi);
        $idxDAY = $this->pxg_get_id_arr_str($ARR_CHI, $_dayChi);
        $_TOA = [];
        $_name = 'Dịch Mã';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            if ($ARR_TOA[$idx] == $_yearChi) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_dayChi) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_monthChi) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
            if ($ARR_TOA[$idx] == $_chi_gio) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $ARR_TOA[$idx], 'year_chi_chu' => $_yearChi, 'info' => $_data_info));
            }
        }
        /*if($idxDAY!=-1){
        if($ARR_TOA[$idxDAY]==$_yearChi){
            array_push($_TOA,array('name'=>$_name,'year'=>$_yearChi,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if($ARR_TOA[$idxDAY]==$_dayChi){
            array_push($_TOA,array('name'=>$_name,'day'=>$_dayChi,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if($ARR_TOA[$idxDAY]==$_monthChi){
            array_push($_TOA,array('name'=>$_name,'month'=>$_monthChi,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
        if($ARR_TOA[$idxDAY]==$_chi_gio){
            array_push($_TOA, array('name'=>$_name,'hour'=>$_chi_gio,'toa'=>$ARR_TOA[$idxDAY],'day_chi_chu'=>$_dayChi));
        }
    }*/
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_LUCTU_EXT($_dayCanChi)
    {
        $LUCTU = ["Mậu Tý", "Kỷ Sửu", "Tân Tỵ", "Đinh Mùi", "Bính Ngọ", "Kỷ Mùi"];
        $idx = $this->pxg_get_id_arr_str($LUCTU, $_dayCanChi);
        $_TOA = [];
        $_name = 'Lục Tú';
        if ($idx != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            array_push($_TOA, array('name' => $_name, 'day' => $_dayCanChi, 'toa' => $LUCTU[$idx], 'info' => $_data_info));
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_TANGMON_EXT($_yearChi, $_dayChi, $_monthChi, $_chi_gio)
    {
        $ARR_TOA = ["Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu", "Tuất", "Hợi", "Tý", "Sửu"];
        $idxDAY = $this->pxg_get_id_arr_str(self::$CHI, $_dayChi);
        $_TOA = [];
        $_name = 'Tang Môn';
        if ($idxDAY != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            $_tmp = $ARR_TOA[$idxDAY];
            if ($_tmp == $_yearChi) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $_tmp, 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if ($_tmp == $_dayChi) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $_tmp, 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if ($_tmp == $_monthChi) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $_tmp, 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if ($_tmp == $_chi_gio) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $_tmp, 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    public function pxg_geofesh_DIEUKHACH_EXT($_dayChi, $_yearChi)
    {
        $ARR_TOA = ["Tuất", "Hợi", "Tý", "Sửu", "Dần", "Mão", "Thìn", "Tỵ", "Ngọ", "Mùi", "Thân", "Dậu"];
        $idxDAY = $this->pxg_get_id_arr_str(self::$CHI, $_dayChi);
        $_TOA = [];
        $_name = 'Điếu Khách';
        if ($idxDAY != -1) {
            $_data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thansat, $_name);
            $_tmp = $ARR_TOA[$idxDAY];
            if ($_tmp == $_yearChi) {
                array_push($_TOA, array('name' => $_name, 'year' => $_yearChi, 'toa' => $_tmp, 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if ($_tmp == $_dayChi) {
                array_push($_TOA, array('name' => $_name, 'day' => $_dayChi, 'toa' => $_tmp, 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if ($_tmp == $_monthChi) {
                array_push($_TOA, array('name' => $_name, 'month' => $_monthChi, 'toa' => $_tmp, 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
            if ($_tmp == $_chi_gio) {
                array_push($_TOA, array('name' => $_name, 'hour' => $_chi_gio, 'toa' => $_tmp, 'day_chi_chu' => $_dayChi, 'info' => $_data_info));
            }
        }
        return (count($_TOA) != 0) ? json_decode(json_encode($_TOA)) : '';
    }
    //TODO: THẦN SÁT CHUYÊN SÂU
    public function pxg_geofesh_VONGTRUONGSINH($_dayCan, $_chi)
    {
        $idxCAN = $this->pxg_get_id_arr_str(self::$CAN, $_dayCan);
        $VONGTRUONGSINH = ["Trường Sinh", "Mộc Dục", "Quan Đới", "Lâm Quan", "Đế Vượng", "Suy", "Bệnh", "Tử", "Mộ", "Tuyệt", "Thai", "Dưỡng"];
        $TRUONGSINH = ["Hợi", "Ngọ", "Dần", "Dậu", "Dần", "Dậu", "Tỵ", "Tý", "Thân", "Mão"];
        $MOCDUC = ["Tý", "Tỵ", "Mão", "Thân", "Mão", "Thân", "Ngọ", "Hợi", "Dậu", "Dần"];
        $QUANDOI = ["Sửu", "Thìn", "Thìn", "Mùi", "Thìn", "Mùi", "Mùi", "Tuất", "Tuất", "Sửu"];
        $LAMQUAN = ["Dần", "Mão", "Tỵ", "Ngọ", "Tỵ", "Ngọ", "Thân", "Dậu", "Hợi", "Tý"];
        $DEVUONG = ["Mão", "Dần", "Ngọ", "Tỵ", "Ngọ", "Tỵ", "Dậu", "Thân", "Tý", "Hợi"];
        $SUY = ["Thìn", "Sửu", "Mùi", "Thìn", "Mùi", "Thìn", "Tuất", "Mùi", "Sửu", "Tuất"];
        $BENH = ["Tỵ", "Tý", "Thân", "Mão", "Thân", "Mão", "Hợi", "Ngọ", "Dần", "Dậu"];
        $TU = ["Ngọ", "Hợi", "Dậu", "Dần", "Dậu", "Dần", "Tý", "Tỵ", "Mão", "Thân"];
        $MO = ["Mùi", "Tuất", "Tuất", "Sửu", "Tuất", "Sửu", "Sửu", "Thìn", "Thìn", "Mùi"];
        $TUYET = ["Thân", "Dậu", "Hợi", "Tý", "Hợi", "Tý", "Dần", "Mão", "Tỵ", "Ngọ"];
        $THAI = ["Dậu", "Thân", "Tý", "Hợi", "Tý", "Hợi", "Mão", "Dần", "Ngọ", "Tỵ"];
        $DUONG = ["Tuất", "Mùi", "Sửu", "Tuất", "Sửu", "Tuất", "Thìn", "Sửu", "Mùi", "Thìn"];
        $VONGTRUONGSINH_CHI = [$TRUONGSINH, $MOCDUC, $QUANDOI, $LAMQUAN, $DEVUONG, $SUY, $BENH, $TU, $MO, $TUYET, $THAI, $DUONG];
        for ($i = 0; $i < count($VONGTRUONGSINH_CHI); $i++) {
            if ($VONGTRUONGSINH_CHI[$i][$idxCAN] == $_chi) {
                return (object)array('can' => $_dayCan, 'chi' => $_chi, 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->truongsinh, $VONGTRUONGSINH[$i]));
            }
        }
        return '';
    }
    public static function pxg_get_day_of_month($mm, $yy)
    {
        $firstday = date('01-' . $mm . '-' . $yy);
        $lastday = date('t', strtotime($firstday));
        return $lastday;
    }
    public function pxg_get_chi_start_dai_van($_monthChi, $khoi_van)
    {
        $CHI_TMP = [];
        $idx_chi = self::pxg_get_id_arr(self::$CHI, $_monthChi);
        if ($khoi_van == 'nghich') {
            $idx_chi = $idx_chi - 1;
            for ($i = $idx_chi; $i >= 0; $i--) {
                array_push($CHI_TMP, (object)array('chi' => self::$CHI[$i], 'nguhanh' => $this->pxg_get_ngu_hanh_by_chi(self::$CHI[$i])));
            }
            for ($i = count(self::$CHI) - 1; $i > $idx_chi; $i--) {
                array_push($CHI_TMP, (object)array('chi' => self::$CHI[$i], 'nguhanh' => $this->pxg_get_ngu_hanh_by_chi(self::$CHI[$i])));
            }
        } else { //thuan
            $idx_chi = $idx_chi + 1;
            for ($i = $idx_chi; $i < count(self::$CHI); $i++) { //Tới 1 năm sau đó tính
                array_push($CHI_TMP, (object)array('chi' => self::$CHI[$i], 'nguhanh' => $this->pxg_get_ngu_hanh_by_chi(self::$CHI[$i])));
            }
            for ($i = 0; $i < $idx_chi; $i++) {
                array_push($CHI_TMP, (object)array('chi' => self::$CHI[$i], 'nguhanh' => $this->pxg_get_ngu_hanh_by_chi(self::$CHI[$i])));
            }
        }
        return $CHI_TMP;
    }
    public function pxg_get_can_start_dai_van($_monthCan, $_dayCan, $khoi_van)
    {
        $CAN_TMP = [];
        $idx_can = self::pxg_get_id_arr(self::$CAN, $_monthCan);
        if ($khoi_van == 'nghich') {
            $idx_can = $idx_can - 1;
            for ($i = $idx_can; $i >= 0; $i--) {
                array_push($CAN_TMP, (object)array('can' => self::$CAN[$i], 'thapthan' => $this->pxg_get_BANGTHAPTHAN($_dayCan, self::$CAN[$i]), 'nguhanh' => $this->pxg_get_ngu_hanh_by_can(self::$CAN[$i])));
            }
            for ($i = count(self::$CAN) - 1; $i > $idx_can; $i--) {
                array_push($CAN_TMP, (object)array('can' => self::$CAN[$i], 'thapthan' => $this->pxg_get_BANGTHAPTHAN($_dayCan, self::$CAN[$i]), 'nguhanh' => $this->pxg_get_ngu_hanh_by_can(self::$CAN[$i])));
            }
        } else { //thuan
            $idx_can = $idx_can + 1;
            for ($i = $idx_can; $i < count(self::$CAN); $i++) { //Tới 1 năm sau đó tính
                array_push($CAN_TMP, (object)array('can' => self::$CAN[$i], 'thapthan' => $this->pxg_get_BANGTHAPTHAN($_dayCan, self::$CAN[$i]), 'nguhanh' => $this->pxg_get_ngu_hanh_by_can(self::$CAN[$i])));
            }
            for ($i = 0; $i < $idx_can; $i++) {
                array_push($CAN_TMP, (object)array('can' => self::$CAN[$i], 'thapthan' => $this->pxg_get_BANGTHAPTHAN($_dayCan, self::$CAN[$i]), 'nguhanh' => $this->pxg_get_ngu_hanh_by_can(self::$CAN[$i])));
            }
        }
        return $CAN_TMP;
    }
    public function pxg_get_date_DAIVAN($dd, $mm, $yy, $khoi_van)
    {
        $age_start_dai_van = 0;
        $ld = $this->getLunarDate($dd, $mm, $yy);
        $_tietKhi = $this->getTietKhi($ld->jd);
        $_monthNongLich = $this->pxg_get_id_arr2_str(self::$TIET12KHI12, $_tietKhi);
        $max_day_cur_month = $this->pxg_get_day_of_month($mm, $yy);
        $_monthNongLichNEXT = 0;
        $_monthNongLichBACK = 0;
        if ($_monthNongLich == 11) {
            $_monthNongLichNEXT = 0;
        } else {
            $_monthNongLichNEXT = $_monthNongLich + 1;
        }
        if ($_monthNongLich == 0) {
            $_monthNongLichBACK = 11;
        } else {
            $_monthNongLichBACK = $_monthNongLich - 1;
        }
        $_tietKhiBACK = self::$TIET12[$_monthNongLichBACK];
        $_tietKhiNEXT = self::$TIET12[$_monthNongLichNEXT];
        $_tietKhiHienTai = self::$TIET12[$_monthNongLich];

        $tietKhiObjectBACK = $this->pxg_geofesh_get_date_by_TIETKHI($_tietKhiBACK, $yy);
        $tietKhiObjectNEXT = $this->pxg_geofesh_get_date_by_TIETKHI($_tietKhiNEXT, $yy);
        $tietKhiObjectHienTai = $this->pxg_geofesh_get_date_by_TIETKHI($_tietKhiHienTai, $yy);
        $str_content = '';
        $date = '';
        if ($khoi_van == 'thuan') {
            if ($mm != $tietKhiObjectNEXT->month) {
                $date = $this->pxg_remain_day($dd, $mm, $yy, $tietKhiObjectNEXT->day, $tietKhiObjectNEXT->month, $yy, $khoi_van);
                $remain_day = $date->remain;
            } else {
                $remain_day = abs($dd - $tietKhiObjectNEXT->day);
            }
            $age_start_dai_van = round($remain_day / 3, 0);
            $str_content = 'Khởi ' . $khoi_van . ' từ ngày ' . $dd . '/' . $mm . ' (' . $date->max_day_1 . ') tiết ' . $_tietKhiHienTai . ' đến ' . $tietKhiObjectNEXT->day . '/' . $tietKhiObjectNEXT->month . ' (' . $date->max_day_2 . ') ' . $_tietKhiNEXT . ' là ' . $remain_day . ' ngày/3 = tuổi khởi vận ' . $age_start_dai_van;
        } else { //nghich
            if ($mm != $tietKhiObjectHienTai->month) {
                $date = $this->pxg_remain_day($dd, $mm, $yy, $tietKhiObjectHienTai->day, $tietKhiObjectHienTai->month, $yy, $khoi_van);
                $remain_day = $date->remain;
            } else {
                $remain_day = abs($dd - $tietKhiObjectBACK->day);
            }
            $age_start_dai_van = round($remain_day / 3, 0);
            $str_content = 'Khởi ' . $khoi_van . ' từ ngày ' . $dd . '/' . $mm . ' (' . $date->max_day_1 . ') tiết ' . $_tietKhiHienTai . ' lùi về ' . $tietKhiObjectHienTai->day . '/' . $tietKhiObjectHienTai->month . ' (' . $date->max_day_2 . ') là ' . $remain_day . ' ngày/3 = tuổi khởi vận ' . $age_start_dai_van;
        }
        return (object)array('age' => $age_start_dai_van, 'content' => $str_content);
    }
    public function pxg_geofesh_get_date_by_TIETKHI($_tietKhi, $yy)
    {
        //Return datetime by $_tietKhi/$yy;
        switch ($_tietKhi) {
            case 'Tiểu Hàn':
                $dd = 5;
                $mm = 1;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Tiểu Hàn') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return (object)array('name' => $_tietKhi, 'day' => $dd, 'month' => $mm);
            case 'Lập Xuân':
                $dd = 3;
                $mm = 2;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Lập Xuân') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return (object)array('name' => $_tietKhi, 'day' => $dd, 'month' => $mm);
            case 'Kinh Trập':
                $dd = 4;
                $mm = 3;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Kinh Trập') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return (object)array('name' => $_tietKhi, 'day' => $dd, 'month' => $mm);
            case 'Thanh Minh':
                $dd = 3;
                $mm = 4;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Thanh Minh') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return (object)array('name' => $_tietKhi, 'day' => $dd, 'month' => $mm);
            case 'Lập Hạ':
                $dd = 4;
                $mm = 5;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Lập Hạ') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return (object)array('name' => $_tietKhi, 'day' => $dd, 'month' => $mm);
            case 'Mang Chủng':
                $dd = 4;
                $mm = 6;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Mang Chủng') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return (object)array('name' => $_tietKhi, 'day' => $dd, 'month' => $mm);
            case 'Tiểu Thử':
                $dd = 6;
                $mm = 7;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Tiểu Thử') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return $dd;
            case 'Lập Thu':
                $dd = 6;
                $mm = 8;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Lập Thu') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return (object)array('name' => $_tietKhi, 'day' => $dd, 'month' => $mm);
            case 'Bạch Lộ':
                $dd = 6;
                $mm = 9;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Bạch Lộ') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return (object)array('name' => $_tietKhi, 'day' => $dd, 'month' => $mm);
            case 'Hàn Lộ':
                $dd = 7;
                $mm = 10;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Hàn Lộ') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return $dd;
            case 'Lập Đông':
                $dd = 6;
                $mm = 11;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Lập Đông') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return (object)array('name' => $_tietKhi, 'day' => $dd, 'month' => $mm);
            case 'Đại Tuyết':
                $dd = 6;
                $mm = 12;
                $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                while ($_tietKhi != 'Đại Tuyết') {
                    $dd++;
                    $_tietKhi = $this->getTietKhi($this->getLunarDate($dd, $mm, $yy)->jd);
                }
                return (object)array('name' => $_tietKhi, 'day' => $dd, 'month' => $mm);
            default:
                return (object)array('name' => '', 'day' => '', 'month' => '');
        }
    }
    public function pxg_remain_day($d1, $m1, $y1, $d2, $m2, $y2, $khoi = 'thuan')
    {
        $max_day_m1 = $this->pxg_get_day_of_month($m1, $y1);
        $max_day_m2 = $this->pxg_get_day_of_month($m2, $y2);
        $remain = ($khoi == 'thuan') ? (($max_day_m1 - $d1) + $d2) : ($d1 + ($max_day_m2 - $d2));
        return (object)array('remain' => $remain, 'max_day_1' => $max_day_m1, 'max_day_2' => $max_day_m2);
    }
    public function pxg_get_TANGCAN_DIACHI($_chi)
    {
        return self::$TANGCAN_DIACHI[self::pxg_get_id_arr(self::$CHI, $_chi)];
    }
    public function pxg_get_tang_can_dia_chi_thap_than($_chi, $_dayCan)
    {
        $tang_can = self::$TANGCAN_DIACHI[self::pxg_get_id_arr(self::$CHI, $_chi)];
        $tang_can_ngu_hanh = array();
        foreach ($tang_can as $key => $_tang_can) {
            $THAPTHAN = $this->pxg_get_BANGTHAPTHAN($_dayCan, $_tang_can);
            array_push($tang_can_ngu_hanh, (object)array('can' => $_tang_can, 'nguhanh' => $this->pxg_get_ngu_hanh_by_can($_tang_can), 'thapthan' => $THAPTHAN));
        }
        return $tang_can_ngu_hanh;
    }
    public function pxg_get_tang_can_by_dia_chi($_chi)
    {
        $tang_can = self::$TANGCAN_DIACHI[self::pxg_get_id_arr(self::$CHI, $_chi)];
        $tang_can_ngu_hanh = array();
        foreach ($tang_can as $key => $_tang_can) {
            array_push($tang_can_ngu_hanh, (object)array('can' => $_tang_can, 'nguhanh' => $this->pxg_get_ngu_hanh_by_can($_tang_can)));
        }
        return $tang_can_ngu_hanh;
    }
    public function pxg_get_ngu_hanh_by_chi($_chi)
    {
        return self::$CHINGUHANH[$this->pxg_get_id_arr(self::$CHI, $_chi)];
    }
    public function pxg_get_ngu_hanh_by_can($_can)
    {
        return self::$CANNGUHANH[$this->pxg_get_id_arr(self::$CAN, $_can)];
    }
    public function pxg_get_BANGTHAPTHAN($_can_chu, $_can)
    { //$_can_gio Giáp,$_yearChi Mão
        //Lấy vị trí thập thần tương ứng trong bảng self::$THAPTHAN_GIAP và lấy tên Thập Thần trong bảng self::$THAPTHAN
        //["Giáp","Ất","Bính","Đinh","Mậu","Kỷ","Canh","Tân","Nhâm","Quý"];
        switch ($_can_chu) {
            case 'Giáp':
                $idx = $this->pxg_get_id_arr(self::$THAPTHAN_GIAP, $_can);
                break;
            case 'Ất':
                $idx = $this->pxg_get_id_arr(self::$THAPTHAN_AT, $_can);
                break;
            case 'Bính':
                $idx = $this->pxg_get_id_arr(self::$THAPTHAN_BINH, $_can);
                break;
            case 'Đinh':
                $idx = $this->pxg_get_id_arr(self::$THAPTHAN_DINH, $_can);
                break;
            case 'Mậu':
                $idx = $this->pxg_get_id_arr(self::$THAPTHAN_MAU, $_can);
                break;
            case 'Kỷ':
                $idx = $this->pxg_get_id_arr(self::$THAPTHAN_KY, $_can);
                break;
            case 'Canh':
                $idx = $this->pxg_get_id_arr(self::$THAPTHAN_CANH, $_can);
                break;
            case 'Tân':
                $idx = $this->pxg_get_id_arr(self::$THAPTHAN_TAN, $_can);
                break;
            case 'Nhâm':
                $idx = $this->pxg_get_id_arr(self::$THAPTHAN_NHAM, $_can);
                break;
            case 'Quý':
                $idx = $this->pxg_get_id_arr(self::$THAPTHAN_QUY, $_can);
                break;
            default:
                break;
        }
        $_name = self::$THAPTHAN[$idx];
        return array('key' => self::$THAPTHANKEY[$idx], 'name' => $_name, 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thapthan, $_name)); //$_can_chu.' - '.$_can.' => '.self::$THAPTHAN[$idx];  
    }
    public function pxg_geofesh($agr)
    {
        extract(shortcode_atts(
            array(
                'name' => '',
                'day' => '',
                'month' => '',
                'year' => '',
                'hour' => '',
                'minute' => '',
                'is_text' => ''
            ),
            $agr
        ));
        //$this->pxg_geofesh_call($agr);

        ob_start();
        $hours = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11', '12', '13', '14', '15', '16', '17', '18', '19', '20', '21', '22', '23'];
        $months = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '10', '11'];
        $minutes = ['59', '58', '57', '56', '55', '54', '53', '52', '51', '50', '49', '48', '47', '46', '45', '44', '43', '42', '41', '40', '39', '38', '37', '36', '35', '34', '33', '32', '31', '30', '29', '28', '27', '26', '25', '24', '23', '22', '21', '20', '19', '18', '17', '16', '15', '14', '13', '12', '11', '10', '9', '8', '7', '6', '5', '4', '3', '2', '1', '0'];
        $curyear = intval(date("Y"));
        for ($_y = $curyear; $_y > 0; $_y--) { //max=2199;
            $years[] = $_y;
        }
?>
        <div class="pxg_geofesh_lasobattu" id="pxg_geofesh_lasobattu" data-logo="<?php echo self::$pxg_geofesh_data->logo_link ?>" data-message="Đang tính Lá Số Tứ Trụ">
            <div class="col-lg-12 row nopad clearfix">
                <div class="col-lg-12 row clearfix nopad" note="Thông tin">
                    <div class="info col-lg-9">
                        <div style="background:#ffffff;padding: 13px;border: 1px solid #ececec;">
                            <div class="pxg_d1">
                                <div class="row clearfix">
                                    <div class="col-lg-8"><input type="text" id="pxg_geo_name" /></div>
                                    <div class="col-lg-4">
                                        <select id="pxg_geo_mf" class="pxg_geo_sel cursor">
                                            <option value="male">Nam</option>
                                            <option value="female">Nữ</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="pxg_d1">
                                <div class="row clearfix">
                                    <div class="col-lg-4">
                                        <select id="pxg_geo_day" class="pxg_geo_sel cursor">
                                            <option value="">--Ngày sinh--</option>
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <select class="pxg_geo_sel cursor" name="pxg_geo_month" id="pxg_geo_month">
                                            <option value="">--Tháng sinh--</option>
                                            <?php
                                            foreach ($months as $key => $mont) : echo '
                                <option value="' . $key . '">' . ((($mont + 1) < 10) ? ('0' . ($mont + 1)) : $mont + 1) . '</option>
                                ';
                                            endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-4">
                                        <select class="pxg_geo_sel cursor" name="pxg_geo_year" id="pxg_geo_year">
                                            <option value="">--Năm sinh--</option>
                                            <?php
                                            foreach ($years as $key => $year) : echo '
                                <option value="' . $year . '">' . (($year < 10) ? '0' . $year : $year) . '</option>
                                ';
                                            endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="pxg_d1">
                                <div class="row clearfix">
                                    <div class="col-lg-6">
                                        <select class="pxg_geo_sel cursor" name="pxg_geo_hour" id="pxg_geo_hour">
                                            <option value="0">--Giờ sinh--</option>
                                            <?php
                                            foreach ($hours as $key => $hour) : echo '
                                <option value="' . $hour . '">' . (($hour < 10) ? ('0' . $hour) : $hour) . '</option>
                                ';
                                            endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-lg-6">
                                        <select class="pxg_geo_sel cursor" name="pxg_geo_minute" id="pxg_geo_minute">
                                            <option value="0">--Phút sinh--</option>
                                            <?php
                                            foreach ($minutes as $key => $minute) : echo '
                                <option value="' . $minute . '">' . (($minute < 10) ? ('0' . $minute) : $minute) . '</option>
                                ';
                                            endforeach; ?>
                                        </select>
                                    </div>
                                </div>
                                <div class="text-center"><span class="btn btn-primary pxg_geofesh_4c">Lấy Lá Số Tứ Trụ</span></div>
                                <?php echo (!empty(self::$pxg_geofesh_data->lasotutru_cta_notice) ? '<div class="pxg_geo_notice text-center col-12">' . self::$pxg_geofesh_data->lasotutru_cta_notice . '</div>' : '') ?>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-3">
                        <!--CurYear-->
                        <div id="pxg_geo_curyear_daivan" class="pxg_geo_curyear_daivan"></div>
                        <!--CurYear-->
                    </div>
                </div>
                <div class="col-lg-12 row nopad clearfix">
                    <div class="col-lg-9">
                        <div id="lasobattu">
                            <table class="pxg_geofesh_lasotutru table-responsive">
                                <tbody>
                                    <tr>
                                        <td class="head">LÁ SỐ BÁT TỰ</td>
                                        <td colspan="3" class="head_cols text-center">TRỤ GIỜ</td>
                                        <td colspan="3" class="head_cols text-center">TRỤ NGÀY</td>
                                        <td colspan="3" class="head_cols text-center">TRỤ THÁNG</td>
                                        <td colspan="3" class="head_cols text-center">TRỤ NĂM</td>
                                    </tr>
                                    <tr id="year_solar">
                                        <td class="head_cols">DƯƠNG LỊCH</td>
                                        <td colspan="3" rowspan="3" class="hour hour_thoitru text-center nopad"></td>
                                        <td colspan="3" class="day text-center nopad"></td>
                                        <td colspan="3" class="month text-center nopad"></td>
                                        <td colspan="3" class="year text-center nopad"></td>
                                    </tr>
                                    <tr id="year_lunar">
                                        <td class="head_cols">ÂM LỊCH</td>
                                        <td colspan="3" class="day text-center nopad"></td>
                                        <td colspan="3" class="month text-center nopad"></td>
                                        <td colspan="3" class="year text-center nopad"></td>
                                    </tr>
                                    <tr id="year_nonglich">
                                        <td class="head_cols">NÔNG LỊCH</td>
                                        <td colspan="3" class="day text-center"></td>
                                        <td colspan="3" class="month text-center"></td>
                                        <td colspan="3" class="year text-center nopad"></td>
                                    </tr>
                                    <tr id="laso_thiencan">
                                        <td class="head_cols">THIÊN CAN</td>
                                        <td colspan="3" class='hour_can text-center nopad'></td>
                                        <td colspan="3" class="day_can text-center nopad"></td>
                                        <td colspan="3" class="month_can text-center nopad"></td>
                                        <td colspan="3" class="year_can text-center nopad"></td>
                                    </tr>
                                    <tr id="laso_diachi">
                                        <td class="head_cols">ĐỊA CHI</td>
                                        <td colspan="3" class="text-center hour nopad"></td>
                                        <td colspan="3" class="text-center day nopad"></td>
                                        <td colspan="3" class="text-center month nopad"></td>
                                        <td colspan="3" class="text-center year nopad"></td>
                                    </tr>
                                    <tr id="laso_tangcan">
                                        <td class="head_cols">TÀNG CAN</td>
                                        <td colspan="3" class="text-center hour nopad"></td>
                                        <td colspan="3" class="text-center day nopad"></td>
                                        <td colspan="3" class="text-center month nopad"></td>
                                        <td colspan="3" class="text-center year nopad"></td>
                                    </tr>
                                    <tr id="laso_napam" style="display:none">
                                        <td class="head_cols">NẠP ÂM</td>
                                        <td colspan="3" class="text-center hour nopad"></td>
                                        <td colspan="3" class="text-center day nopad"></td>
                                        <td colspan="3" class="text-center month nopad"></td>
                                        <td colspan="3" class="text-center year nopad"></td>
                                    </tr>
                                    <tr id="laso_truongsinh" style="display:none">
                                        <td class="head_cols">TRƯỜNG SINH</td>
                                        <td colspan="3" class="text-center hour nopad"></td>
                                        <td colspan="3" class="text-center day nopad"></td>
                                        <td colspan="3" class="text-center month nopad"></td>
                                        <td colspan="3" class="text-center year nopad"></td>
                                    </tr>
                                    <tr id="laso_thansat" style="display:none">
                                        <td class="head_cols">THẦN SÁT</td>
                                        <td colspan="3" class="text-center hour nopad"></td>
                                        <td colspan="3" class="text-center day nopad"></td>
                                        <td colspan="3" class="text-center month nopad"></td>
                                        <td colspan="3" class="text-center year nopad"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="pxg_geofesh_info_thapthan col-12 text-center" style="margin-bottom:13px"></div>
                        <!--ĐẠI VẬN-->
                        <div id="daivan">
                            <table class="table-responsive">
                                <tbody>
                                    <tr id="tuoidaivan"></tr>
                                    <tr id="canchidaivan"></tr>
                                    <tr id="tangcandaivan"></tr>
                                    <tr id="daivannapam" style="display:none"></tr>
                                    <tr id="daivantruongsinh" style="display:none"></tr>
                                    <tr id="daivanthansat" style="display:none"></tr>
                                </tbody>
                            </table>
                        </div>
                        <div id="chukydaivan"></div>
                        <!--//ĐẠI VẬN-->
                    </div>
                    <div class="col-lg-3">
                        <!--Compass-->
                        <div id="pxg_geo_compass_zodiac" class="pxg_geo_compass_zodiac"></div>
                        <!--Compass-->
                        <!--ThanSat-->
                        <div id="pxg_geo_thansat"></div>
                        <!--ThanSat-->
                        <!--ThaiCungMenhCung-->
                        <div id="pxg_geo_thaimenhcung"></div>
                        <!--ThaiCungMenhCung-->
                    </div>
                </div>
            </div>
            <?php $this->pxg_geofesh_generate_popup('pxg_geofesh_date_tutru', '99999'); ?>
            <?php $this->pxg_geofesh_generate_popup('pxg_geofesh_cdaivan', '9999'); ?>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var _cur_date = new Date();
                var _current_year = '<?php echo (!empty($agr['year']) ? $agr['year'] : '') ?>';
                _current_year = (_current_year !== '') ? _current_year : _cur_date.getFullYear();

                var _current_month = '<?php echo (!empty($agr['month']) ? $agr['month'] : '') ?>';
                _current_month = (_current_month !== '') ? _current_month - 1 : _cur_date.getMonth();

                var _current_day = '<?php echo (!empty($agr['day']) ? $agr['day'] : '') ?>';
                _current_day = (_current_day !== '') ? _current_day : _cur_date.getDate();

                var _current_hour = '<?php echo (!empty($agr['hour']) ? $agr['hour'] : '') ?>';
                _current_hour = (_current_hour !== '') ? _current_hour : _cur_date.getHours();

                var _current_minute = '<?php echo (!empty($agr['minute']) ? $agr['minute'] : '') ?>';
                _current_minute = (_current_minute !== '') ? _current_minute : _cur_date.getMinutes();


                var _current_name = '<?php echo (!empty($agr['name']) ? $agr['name'] : self::$pxg_geofesh_data->lasotutru_name_default) ?>';

                $('#pxg_geo_hour').val(_current_hour);
                $('#pxg_geo_minute').val(_current_minute);

                $('#pxg_geo_year').val(_current_year);
                $('#pxg_geo_month').val(_current_month);
                pxg_geo_bind_day('pxg_geo_day', _current_month, _current_year);
                $('#pxg_geo_day').val(_current_day);
                $('#pxg_geo_name').val(_current_name);
                pxg_geofesh();
                var pxg_geo_month = '';
                var pxg_geo_year = '';
                $("#pxg_geo_month").change(function() {
                    pxg_geo_month = $('#pxg_geo_month').val() - 0;
                    pxg_geo_year = $('#pxg_geo_year').val() - 0;
                    pxg_geo_bind_day('pxg_geo_day', pxg_geo_month, pxg_geo_year);
                });
                jQuery('.pxg_geofesh_4c').click(function(e) {
                    pxg_geofesh(true);
                });
                var _tutru;
                var thien_tru;
                var nguyet_tru;
                var nhat_tru;
                var thoi_tru;
                var _thap_than;
                var _napam_daivan;
                var _truongsinh_daivan;
                var _thansat_daivan;
                var _thansat_daivan2;
                var _is_fee = false;
                var _display = 'display:none';
                var _good;
                var _bad;
                var _daivan_currentyear;
                var _daivan_cyear;

                function pxg_geo_des_info(_name, _data) {
                    if (_data != null || _data != undefined) {
                        for (var i = _data.length - 1; i >= 0; i--) {
                            if (_data[i].code == _name) {
                                return _data[i];
                            }
                        };
                    }
                    return null;
                }
                jQuery(document).on('click', '.pxg_geo_des', function(eve) {
                    var _data = $(this).data('info');
                    if (_data == undefined) return;
                    var _type = $(this).data('type');
                    if (_type == undefined) {
                        pxg_geofesh_popup('pxg_geofesh_date_tutru', _data.name, _data.description);
                    } else {
                        var _data_info = pxg_geo_des_info(_data, _thap_than);
                        pxg_geofesh_popup('pxg_geofesh_date_tutru', _data, ((_data_info != null) ? '<div>' + _data_info.sid + '</div>' : '') + ((_data_info != null) ? _data_info.content : ''));
                    }
                });
                jQuery(document).on('click', '.pxg_geo_des_by_year', function(eve) {
                    var _type = $(this).data('type');
                    var _data;
                    var _id = $(this).data('id');
                    if (_type == 'vongtruongsinh') {
                        _data = _daivan_currentyear.vongtruongsinh.info;
                    } else if (_type == 'cvongtruongsinh') {
                        _data = _daivan_cyear.vongtruongsinh.info;
                    } else if (_type == 'napam') {
                        _data = _daivan_currentyear.napam.info;
                    } else if (_type == 'cnapam') {
                        _data = _daivan_cyear.napam.info;
                    } else if (_type == 'thansat') {
                        if (_id == undefined || _daivan_currentyear.thansat[_id] == undefined) return;
                        _data = _daivan_currentyear.thansat[_id].info;
                    } else if (_type == 'cthansat') {
                        if (_id == undefined || _daivan_cyear.thansat[_id] == undefined) return;
                        _data = _daivan_cyear.thansat[_id].info;
                    }
                    if (_data == undefined) return;
                    pxg_geofesh_popup('pxg_geofesh_date_tutru', _data.name, _data.description);
                });
                jQuery(document).on('click', '.pxg_geofesh_sdaivan_info', function(eve) {
                    var _year = $(eve.target).data('year');
                    var _can_chi = $(eve.target).data('canchi');
                    if (_year == undefined) {
                        eve.preventDefault();
                        return;
                    }
                    var _load = 'pxg_daivan_' + _year;
                    $(eve.target).append('<div class="' + _load + ' pxgdvcal">Đang tính đại vận...</div>');
                    var data = {
                        'action': 'pxg_geofesh_sdaivan_call',
                        'd': $('#pxg_geo_day').val(),
                        'm': $('#pxg_geo_month').val(),
                        'y': _year,
                        'ym': $('#pxg_geo_year').val(),
                        'h': $('#pxg_geo_hour').val(),
                        'mi': $('#pxg_geo_minute').val(),
                    };
                    jQuery.post(spyr_params.ajaxurl, data, function(_testRest) {
                        var _htmlCurY = '';
                        _daivan_cyear = '';
                        _daivan_cyear = jQuery.parseJSON(_testRest);
                        _htmlCurY += '<table id="lasobattu_ccyear" class="lasobattu_cyear xtable"><tbody>';
                        _htmlCurY += '<tr><td class="head_cols">THIÊN CAN</td><td colspan="3" class="year_can text-center nopad"><div class="bbot1 pxg_bl"><span class="year_thapthan pxg_geo_des" data-type="cthapthan" data-info="' + _daivan_cyear.year_can_thap_than.name + '" title="' + _daivan_cyear.year_can_thap_than.name + '">' + _daivan_cyear.year_can_thap_than.key + '</span> | <span class="bb1">' + _daivan_cyear.can + '</span></div><div>' + _daivan_cyear.year_can_nguhanh + '</div></td></tr>';
                        _htmlCurY += '<tr><td class="head_cols">ĐỊA CHI</td><td colspan="3" class="text-center year nopad"><div class="bbot1 bb1 pxg_bl">' + _daivan_cyear.chi + '</div><div>' + _daivan_cyear.year_chi_nguhanh + '</div></td></tr>';
                        var _cur_year_tang_can = '';
                        for (var _i = 0; _i < _daivan_cyear.tangcan.length; _i++) {
                            _cur_year_tang_can += '<div class="tang_can"><div class="bbot1 pxg_bl cursor pxg_geo_des" data-id="' + _i + '" data-type="cthapthan" data-info="' + _daivan_cyear.tangcan[_i].thapthan.name + '">' + _daivan_cyear.tangcan[_i].thapthan.key + '</div><div class="tccan">' + _daivan_cyear.tangcan[_i].can + '</div><div class="tcnguhanh">' + _daivan_cyear.tangcan[_i].nguhanh + '</div></div>';
                        }
                        _htmlCurY += '<tr><td class="head_cols">TÀNG CAN</td><td colspan="3" class="text-center year nopad">' + _cur_year_tang_can + '</td></tr>';
                        if (_is_fee == true) {
                            _htmlCurY += '<tr style="' + _display + '"><td class="head_cols">NẠP ÂM</td><td colspan="3" class="text-center year nopad"><div class="_napam pxg_geo_des_by_year cursor" data-type="cnapam">' + _daivan_cyear.napam.name + '</div></td></tr>';
                            _htmlCurY += '<tr style="' + _display + '"><td class="head_cols">TRƯỜNG SINH</td><td colspan="3" class="text-center year nopad"><div class="_thansat"><div class="pxg_bl pxg_geo_des_by_year cursor" data-type="cvongtruongsinh">' + _daivan_cyear.vongtruongsinh.info.name + '</div></div></td></tr>';
                            var _thansat_cyear = _daivan_cyear.thansat;
                            var _html_thansat_daivan_cyear = '';
                            if (_thansat_cyear != undefined) {
                                for (var _i = 0; _i < _thansat_cyear.length; _i++) {
                                    var _cyear_ts = '';
                                    _cyear_ts += '<div class="_thansat"><div class="pxg_bl pxg_geo_des_by_year cursor" data-type="cthansat" data-id="' + _i + '">' + _thansat_cyear[_i].name + '</div></div>';
                                    _html_thansat_daivan_cyear += _cyear_ts;
                                }
                            }
                            _htmlCurY += '<tr style="' + _display + '"><td class="head_cols">THẦN SÁT</td><td colspan="3" class="text-center year nopad">' + _html_thansat_daivan_cyear + '</td></tr>';
                        }
                        _htmlCurY += '</tbody></table>';
                        $(eve.target).find('.' + _load).remove();
                        pxg_geofesh_popup('pxg_geofesh_cdaivan', 'Năm ' + _year + ' ' + _can_chi, _htmlCurY, 'pop_geofesh_cyear_daivan');
                    });
                });
                jQuery(document).on('click', '.pxg_geofesh_infox', function(eve) {
                    var _info = $(this).data('info');
                    var _type = $(this).data('type');
                    var _data;
                    var _id = $(this).data('id');
                    switch (_type) {
                        case 'nien': {
                            if (_info == 'vongtruongsinh') {
                                _data = _tutru.thien_tru.vongtruongsinh.info;
                            } else if (_info == 'napam') {
                                _data = _tutru.thien_tru.napam.info;
                            } else if (_info == 'thansat') {
                                if (_id == undefined || _tutru.thien_tru.thansat[_id] == undefined) return;
                                _data = _tutru.thien_tru.thansat[_id].info;
                            } else if (_info == 'thansat2') {
                                if (_id == undefined || _thansat_daivan2[_id] == undefined) return;
                                _data = _thansat_daivan2[_id].info;
                            }
                            break;
                        }
                        case 'nguyet': {
                            if (_info == 'vongtruongsinh') {
                                _data = _tutru.nguyet_tru.vongtruongsinh.info;
                            } else if (_info == 'napam') {
                                _data = _tutru.nguyet_tru.napam.info;
                            } else if (_info == 'thansat') {
                                if (_id == undefined || _tutru.nguyet_tru.thansat[_id] == undefined) return;
                                _data = _tutru.nguyet_tru.thansat[_id].info;
                            }
                            break;
                        }
                        case 'nhat': {
                            if (_info == 'vongtruongsinh') {
                                _data = _tutru.nhat_tru.vongtruongsinh.info;
                            } else if (_info == 'napam') {
                                _data = _tutru.nhat_tru.napam.info;
                            } else if (_info == 'thansat') {
                                if (_id == undefined || _tutru.nhat_tru.thansat[_id] == undefined) return;
                                _data = _tutru.nhat_tru.thansat[_id].info;
                            }
                            break;
                        }
                        case 'thoi': {
                            if (_info == 'vongtruongsinh') {
                                _data = _tutru.thoi_tru.vongtruongsinh.info;
                            } else if (_info == 'napam') {
                                _data = _tutru.thoi_tru.napam.info;
                            } else if (_info == 'thansat') {
                                if (_id == undefined || _tutru.thoi_tru.thansat[_id] == undefined) return;
                                _data = _tutru.thoi_tru.thansat[_id].info;
                            }
                            break;
                        }
                        case 'daivan': {
                            if (_info == 'vongtruongsinh') {
                                if (_id == undefined || _truongsinh_daivan[_id] == undefined) return;
                                _data = _truongsinh_daivan[_id].info;
                            } else if (_info == 'napam') {
                                if (_id == undefined || _napam_daivan[_id] == undefined) return;
                                _data = _napam_daivan[_id].info;
                            } else if (_info == 'thansat') {
                                if (_id == undefined || _thansat_daivan[_id] == undefined) return;
                                var _id2 = $(this).data('idx');
                                _data = _thansat_daivan[_id][_id2].info;
                            }
                            break;
                        }
                        case 'compass': {
                            if (_info == 'bad') {
                                if (_id == undefined || _bad[_id] == undefined) return;
                                _data = _bad[_id].info;
                            } else if (_info == 'good') {
                                if (_id == undefined || _good[_id] == undefined) return;
                                _data = _good[_id].info;
                            }
                            break;
                        }
                    }
                    if (_data == undefined) return;
                    pxg_geofesh_popup('pxg_geofesh_date_tutru', _data.name, _data.description);
                });

                function pxg_geofesh(_is_load = false) {
                    var _lasobattu = $('#lasobattu');
                    if (_lasobattu.length == 0) return;
                    //THAPTHAN
                    _thap_than = <?php echo json_encode(self::$pxg_geofesh_data->thapthan) ?>;
                    if (_thap_than != null || _thap_than != undefined) {
                        var _ttif = [];
                        for (var i = _thap_than.length - 1; i >= 0; i--) {
                            var _tt = RemoveVN(_thap_than[i].code);
                            var _ttc = '';
                            var _arr = _tt.split(' ');
                            if (_arr.length == 2) {
                                if (_tt == 'Thuc Than') {
                                    _ttc = 'TH';
                                } else {
                                    _ttc = _arr[0][0] + _arr[1][0];
                                }
                            }
                            _ttc = _ttc.toUpperCase();
                            _ttif[i] = '<span class="pxg_geo_des" data-type="thapthan" data-info="' + _thap_than[i].code + '" title="' + _thap_than[i].sid + '"><strong>' + _ttc + '</strong>: ' + _thap_than[i].code + '</span>';
                        };
                        $('.pxg_geofesh_info_thapthan').html('<strong>Thập Thần</strong>: ' + _ttif.join(', '));
                    }
                    //THAPTHAN
                    var pxg_geo_name = $('#pxg_geo_name').val();
                    var pxg_geo_day = $('#pxg_geo_day').val();
                    pxg_geo_month = $('#pxg_geo_month').val();
                    pxg_geo_year = $('#pxg_geo_year').val();
                    if (pxg_geo_name == '' || pxg_geo_day == '' || pxg_geo_month == '' || pxg_geo_year == '') {
                        pxg_geofesh_popup('pxg_geofesh_date_tutru', 'Lưu ý', 'Vui lòng kiểm tra và nhập thông tin: TÊN, NGÀY, THÁNG, NĂM của bạn!');
                        return;
                    }
                    var pxg_geo_mf = $('#pxg_geo_mf').val();
                    var pxg_geo_hour = $('#pxg_geo_hour').val();
                    var pxg_geo_minute = $('#pxg_geo_minute').val();
                    var data = {
                        'action': 'pxg_geofesh_call',
                        'n': pxg_geo_name,
                        's': pxg_geo_mf,
                        'd': pxg_geo_day,
                        'm': pxg_geo_month,
                        'y': pxg_geo_year,
                        'ym': pxg_geo_year,
                        'h': pxg_geo_hour,
                        'mi': pxg_geo_minute,
                    };
                    if (_is_load == true) {
                        var _message_waiting = $('#pxg_geofesh_lasobattu').data('message');
                        var _logo_src = $('#pxg_geofesh_lasobattu').data('logo');

                        pxg_notice_add_waiting('pxg_is_loading', ((_logo_src != '') ? '<div><img src="' + _logo_src + '"/></div>' : '') + '<div>' + _message_waiting + '</div>');
                    }
                    jQuery.post(spyr_params.ajaxurl, data, function(_testRest) {
                        var _rest_objs = jQuery.parseJSON(_testRest)[0];
                        var _compass = _rest_objs.compass;
                        _is_fee = _rest_objs.is_fee;
                        _display = (_is_fee == false) ? 'display:none' : 'display:';
                        _thansat_daivan2 = _rest_objs.thansat;
                        _tutru = _rest_objs.tutru;
                        var _years = _rest_objs.date; /*solar & lunar*/
                        _good = _compass[0].good;
                        _bad = _compass[0].bad;
                        var _htmlPH = '';
                        var _htmlCZ = '<tr><td class="_name">[[NAME]]</td><td class="_val">[[VAL]]</td></tr>';
                        var _good_h = '',
                            _bad_h = '';
                        for (var i = 0; i < _good.length; i++) {
                            var tmp = _htmlCZ.replace('[[NAME]]', '<div class="pxg_bl pxg_geofesh_infox" data-type="compass" data-info="good" data-id="' + i + '">' + _good[i].name + '</div>');
                            if (i == 0) {
                                tmp = tmp.replace('<tr>', '<tr><td rowspan="4" class="pxg_geo_middle _good">TỐT</td>');
                            }
                            tmp = tmp.replace('[[VAL]]', _good[i].compass + '<div class="zodiac5els">(' + _good[i].zodiac + '-' + _good[i].five_elements + ')</div>');
                            _good_h = _good_h + tmp;
                        };
                        for (var i = 0; i < _bad.length; i++) {
                            var tmp = _htmlCZ.replace('[[NAME]]', '<div class="pxg_bl pxg_geofesh_infox" data-type="compass" data-info="bad" data-id="' + i + '">' + _bad[i].name + '</div>');
                            if (i == 0) {
                                tmp = tmp.replace('<tr>', '<tr><td rowspan="4" class="pxg_geo_middle _bad">XẤU</td>');
                            }
                            tmp = tmp.replace('[[VAL]]', _bad[i].compass + '<div class="zodiac5els">(' + _bad[i].zodiac + '-' + _bad[i].five_elements + ')</div>');
                            _bad_h = _bad_h + tmp;
                        };
                        _htmlPH = '<table class="xtable"><thead><tr class="phh"><th colspan="3">Phương Hướng</th></tr></thead><tbody>' + _good_h + _bad_h + '</tbody></table>';
                        $('#pxg_geo_compass_zodiac').html(_htmlPH);
                        //TODO: Thần Sát
                        var _htmlTS = '<table class="xtable"><thead><tr class="tsh"><th colspan="3">Thần Sát</th></tr></thead><tbody>[[THANSAT]]</tbody></table>';
                        var _htmlTSi = ''; //<tr style="text-align: center;"><td>[[NAME]]</td><td>[[VAL]]</td></tr>';
                        for (var _ts = 0; _ts < _thansat_daivan2.length; _ts++) {
                            _htmlTSi = _htmlTSi + '<tr><td class="tsn"><div class="pxg_bl pxg_geofesh_infox" data-type="nien" data-info="thansat2" data-id="' + _ts + '">' + _thansat_daivan2[_ts].name + '</div></td><td class="tsd">' + _thansat_daivan2[_ts].day + '</td><td class="tsy">' + _thansat_daivan2[_ts].year + '</td></tr>';
                        }
                        _htmlTS = _htmlTS.replace('[[THANSAT]]', _htmlTSi);
                        $('#pxg_geo_thansat').html(_htmlTS);
                        //THAI CUNG - MỆNH CUNG
                        if (_is_fee == true) {
                            var _htmlTHAICUNG_MENHCUNG = '';
                            _htmlTHAICUNG_MENHCUNG = '<table class="xtable"><thead><tr class="tsh"><th>Thai Cung</th><th>Mệnh Cung</th></tr></thead><tbody><tr><td class="tsd">' + _rest_objs.thaimenhcung.thaicung.can + '</td><td class="tsy">' + _rest_objs.thaimenhcung.menhcung.can + '</td></tr><tr><td class="tsd">' + _rest_objs.thaimenhcung.thaicung.chi + '</td><td class="tsy">' + _rest_objs.thaimenhcung.menhcung.chi + '</td></tr></tbody></table>';
                            $('#pxg_geo_thaimenhcung').html(_htmlTHAICUNG_MENHCUNG);
                        } else {
                            $('#pxg_geo_thaimenhcung').html('');
                        }
                        //LASO_CURRENTYEAR
                        var _htmlCurY = '';
                        _daivan_currentyear = _rest_objs.daivan.current_year;
                        _htmlCurY += '<table id="lasobattu_cyear" class="lasobattu_cyear xtable"><tbody><tr><td colspan="3" class="head text-center">' + _current_year + '</td></tr>';
                        _htmlCurY += '<tr><td class="head_cols">THIÊN CAN</td><td colspan="3" class="year_can text-center nopad"><div class="bbot1 pxg_bl"><span class="year_thapthan pxg_geo_des" data-type="thapthan" data-info="' + _daivan_currentyear.year_can_thap_than.name + '" title="' + _daivan_currentyear.year_can_thap_than.name + '">' + _daivan_currentyear.year_can_thap_than.key + '</span> | <span class="bb1">' + _daivan_currentyear.can + '</span></div><div>' + _daivan_currentyear.year_can_nguhanh + '</div></td></tr>';
                        _htmlCurY += '<tr><td class="head_cols">ĐỊA CHI</td><td colspan="3" class="text-center year nopad"><div class="bbot1 bb1 pxg_bl">' + _daivan_currentyear.chi + '</div><div>' + _daivan_currentyear.year_chi_nguhanh + '</div></td></tr>';
                        var _cur_year_tang_can = '';
                        for (var _i = 0; _i < _daivan_currentyear.tangcan.length; _i++) {
                            _cur_year_tang_can += '<div class="tang_can"><div class="bbot1 pxg_bl cursor pxg_geo_des" data-id="' + _i + '" data-type="thapthan" data-info="' + _daivan_currentyear.tangcan[_i].thapthan.name + '">' + _daivan_currentyear.tangcan[_i].thapthan.key + '</div><div class="tccan">' + _daivan_currentyear.tangcan[_i].can + '</div><div class="tcnguhanh">' + _daivan_currentyear.tangcan[_i].nguhanh + '</div></div>';
                        }
                        _htmlCurY += '<tr><td class="head_cols">TÀNG CAN</td><td colspan="3" class="text-center year nopad">' + _cur_year_tang_can + '</td></tr>';
                        if (_is_fee == true) {
                            _htmlCurY += '<tr style="' + _display + '"><td class="head_cols">NẠP ÂM</td><td colspan="3" class="text-center year nopad"><div class="_napam pxg_geo_des_by_year cursor" data-type="napam">' + _daivan_currentyear.napam.name + '</div></td></tr>';
                            _htmlCurY += '<tr style="' + _display + '"><td class="head_cols">TRƯỜNG SINH</td><td colspan="3" class="text-center year nopad"><div class="_thansat"><div class="pxg_bl pxg_geo_des_by_year cursor" data-type="vongtruongsinh">' + _daivan_currentyear.vongtruongsinh.info.name + '</div></div></td></tr>';
                            var _thansat_cyear = _daivan_currentyear.thansat;
                            var _html_thansat_daivan_cyear = '';
                            if (_thansat_cyear != undefined) {
                                for (var _i = 0; _i < _thansat_cyear.length; _i++) {
                                    var _cyear_ts = '';
                                    _cyear_ts += '<div class="_thansat"><div class="pxg_bl pxg_geo_des_by_year cursor" data-type="thansat" data-id="' + _i + '">' + _thansat_cyear[_i].name + '</div></div>';
                                    _html_thansat_daivan_cyear += _cyear_ts;
                                }
                            }
                            _htmlCurY += '<tr style="' + _display + '"><td class="head_cols">THẦN SÁT</td><td colspan="3" class="text-center year nopad">' + _html_thansat_daivan_cyear + '</td></tr>';
                        }
                        _htmlCurY += '</tbody></table>';
                        $('#pxg_geo_curyear_daivan').html(_htmlCurY);
                        //LASO_CURRENTYEAR

                        //TODO: Lá Số Bát Tự
                        thien_tru = _tutru.thien_tru;
                        nguyet_tru = _tutru.nguyet_tru;
                        nhat_tru = _tutru.nhat_tru;
                        thoi_tru = _tutru.thoi_tru;

                        $('#year_solar .year').html(_years.solar.year);
                        $('#year_solar .month').html(_years.solar.month);
                        $('#year_solar .day').html(_years.solar.day);
                        $('#year_lunar .year').html(_years.lunar.year);
                        $('#year_lunar .month').html(_years.lunar.month);
                        $('#year_lunar .day').html(_years.lunar.day);
                        $('#year_nonglich .year').html(_years.nonglich.year);
                        $('#year_nonglich .month').html(_years.nonglich.month);
                        $('#year_nonglich .day').html(_years.nonglich.day);

                        $('#year_solar .hour').html((pxg_geo_hour != '' || pxg_geo_minute != '') ? (pxg_geo_hour + ':' + pxg_geo_minute) : '');

                        $('#laso_thiencan .year_can').html('<div class="bbot1 pxg_bl"><span class="year_thapthan pxg_geo_des" data-type="thapthan" data-info="' + thien_tru.year_can_thap_than.name + '" title="' + thien_tru.year_can_thap_than.name + '">' + thien_tru.year_can_thap_than.key + '</span> | <span class="bb1">' + thien_tru.year_can + '</span></div><div>' + thien_tru.year_can_nguhanh + '</div>');
                        $('#laso_thiencan .month_can').html('<div class="bbot1 pxg_bl"><span class="month_thapthan pxg_geo_des" data-type="thapthan" data-info="' + nguyet_tru.month_can_thap_than.name + '" title="' + nguyet_tru.month_can_thap_than.name + '">' + nguyet_tru.month_can_thap_than.key + '</span> | <span class="bb1">' + nguyet_tru.month_can + '</span></div><div>' + nguyet_tru.month_can_nguhanh + '</div>');
                        $('#laso_thiencan .day_can').html('<div class="bbot1 pxg_bl"><span class="day_thapthan pxg_geo_des" data-type="thapthan" data-info="' + nhat_tru.day_can_thap_than.name + '" title="' + nhat_tru.day_can_thap_than.name + '">' + nhat_tru.day_can_thap_than.key + '</span> | <span class="bb1">' + nhat_tru.day_can + '</span></div><div>' + nhat_tru.day_can_nguhanh + '</div>');
                        $('#laso_diachi .year').html('<div class="bbot1 bb1 pxg_bl">' + thien_tru.year_chi + '</div><div>' + thien_tru.year_chi_nguhanh + '</div>');
                        $('#laso_diachi .month').html('<div class="bbot1 bb1 pxg_bl">' + nguyet_tru.month_chi + '</div><div>' + nguyet_tru.month_chi_nguhanh + '</div>');
                        $('#laso_diachi .day').html('<div class="bbot1 bb1 pxg_bl">' + nhat_tru.day_chi + '</div><div>' + nhat_tru.day_chi_nguhanh + '</div>');

                        //laso_tangcan
                        var _year_tang_can = '';
                        for (var _i = 0; _i < thien_tru.year_thap_than.length; _i++) {
                            _year_tang_can += '<div class="tang_can"><div class="bbot1 pxg_bl pxg_geo_des" data-type="thapthan" data-info="' + thien_tru.year_thap_than[_i].name + '">' + thien_tru.year_thap_than[_i].key + '</div><div class="tccan">' + thien_tru.year_tang_can_chi_nguhanh[_i].can + '</div><div class="tcnguhanh">' + thien_tru.year_tang_can_chi_nguhanh[_i].nguhanh + '</div></div>';
                        }
                        $('#laso_tangcan .year').html(_year_tang_can);
                        var _month_tang_can = '';
                        for (var _i = 0; _i < nguyet_tru.month_thap_than.length; _i++) {
                            _month_tang_can = _month_tang_can + '<div class="tang_can"><div class="bbot1 pxg_bl pxg_geo_des" data-type="thapthan" data-info="' + nguyet_tru.month_thap_than[_i].name + '">' + nguyet_tru.month_thap_than[_i].key + '</div><div class="tccan">' + nguyet_tru.month_tang_can_chi_nguhanh[_i].can + '</div><div class="tcnguhanh">' + nguyet_tru.month_tang_can_chi_nguhanh[_i].nguhanh + '</div></div>';
                        }
                        $('#laso_tangcan .month').html(_month_tang_can);

                        var _day_tang_can = '';
                        for (var _i = 0; _i < nhat_tru.day_thap_than.length; _i++) {
                            _day_tang_can += '<div class="tang_can"><div class="bbot1 pxg_bl pxg_geo_des" data-type="thapthan" data-info="' + nhat_tru.day_thap_than[_i].name + '">' + nhat_tru.day_thap_than[_i].key + '</div><div class="tccan">' + nhat_tru.day_tang_can_chi_nguhanh[_i].can + '</div><div class="tcnguhanh">' + nhat_tru.day_tang_can_chi_nguhanh[_i].nguhanh + '</div></div>';
                        }
                        $('#laso_tangcan .day').html(_day_tang_can);
                        if (pxg_geo_hour != '' || pxg_geo_minute != '') {
                            $('#laso_thiencan .hour_can').html('<div class="bbot1 pxg_bl"><span class="hour_thapthan pxg_geo_des" data-type="thapthan" data-info="' + thoi_tru.hour_can_thap_than.name + '" title="' + thoi_tru.hour_can_thap_than.name + '">' + thoi_tru.hour_can_thap_than.key + '</span> | <span class="bb1">' + thoi_tru.hour_can + '</span></div><div>' + thoi_tru.hour_can_nguhanh + '</div>');
                            $('#laso_diachi .hour').html('<div class="bbot1 bb1 pxg_bl">' + thoi_tru.hour_chi + '</div><div>' + thoi_tru.hour_chi_nguhanh + '</div>');
                            var _hour_tang_can = '';
                            for (var _i = 0; _i < thoi_tru.hour_thap_than.length; _i++) {
                                _hour_tang_can += '<div class="tang_can"><div class="bbot1 pxg_bl pxg_geo_des" data-type="thapthan" data-info="' + thoi_tru.hour_thap_than[_i].name + '">' + thoi_tru.hour_thap_than[_i].key + '</div><div class="tccan">' + thoi_tru.hour_tang_can_chi_nguhanh[_i].can + '</div><div class="tcnguhanh">' + thoi_tru.hour_tang_can_chi_nguhanh[_i].nguhanh + '</div></div>';
                            }
                            $('#laso_tangcan .hour').html(_hour_tang_can);
                        }
                        //laso_thansat
                        if (_is_fee == false) {
                            $('#laso_napam').css('display', 'none');
                            $('#laso_thansat').css('display', 'none');
                            $('#laso_truongsinh').css('display', 'none');
                        } else {
                            $('#laso_napam').css('display', '');
                            $('#laso_thansat').css('display', '');
                            $('#laso_truongsinh').css('display', '');
                            var _year_than_sat = '';
                            for (var _i = 0; _i < thien_tru.thansat.length; _i++) {
                                _year_than_sat += '<div class="_thansat"><div class="pxg_bl pxg_geofesh_infox" data-type="nien" data-info="thansat" data-id="' + _i + '">' + thien_tru.thansat[_i].name + '</div></div>';
                            }
                            $('#laso_thansat .year').html(_year_than_sat);
                            var _month_than_sat = '';
                            for (var _i = 0; _i < nguyet_tru.thansat.length; _i++) {
                                _month_than_sat += '<div class="_thansat"><div class="pxg_bl pxg_geofesh_infox" data-type="nguyet" data-info="thansat" data-id="' + _i + '">' + nguyet_tru.thansat[_i].name + '</div></div>';
                            }
                            $('#laso_thansat .month').html(_month_than_sat);
                            var _day_than_sat = '';
                            for (var _i = 0; _i < nhat_tru.thansat.length; _i++) {
                                _day_than_sat += '<div class="_thansat"><div class="pxg_bl pxg_geofesh_infox" data-type="nhat" data-info="thansat" data-id="' + _i + '">' + nhat_tru.thansat[_i].name + '</div></div>';
                            }
                            $('#laso_thansat .day').html(_day_than_sat);
                            if (pxg_geo_hour != '' || pxg_geo_minute != '') {
                                var _hour_than_sat = '';
                                for (var _i = 0; _i < thoi_tru.thansat.length; _i++) {
                                    _hour_than_sat += '<div class="_thansat"><div class="pxg_bl pxg_geofesh_infox" data-type="thoi" data-info="thansat" data-id="' + _i + '">' + thoi_tru.thansat[_i].name + '</div></div>';
                                }
                                $('#laso_thansat .hour').html(_hour_than_sat);
                                $('#laso_napam .hour').html('<div class="_napam pxg_geofesh_infox" data-type="thoi" data-info="napam">' + thoi_tru.napam.name + '</div>');
                                $('#laso_truongsinh .hour').html('<div class="_thansat"><div class="pxg_bl pxg_geofesh_infox" data-type="thoi" data-info="vongtruongsinh">' + thoi_tru.vongtruongsinh.info.name + '</div></div>');
                            }
                            //laso_napam
                            $('#laso_napam .year').html('<div class="_napam pxg_geofesh_infox" data-type="nien" data-info="napam">' + thien_tru.napam.name + '</div>');
                            $('#laso_napam .month').html('<div class="_napam pxg_geofesh_infox" data-type="nguyet" data-info="napam">' + nguyet_tru.napam.name + '</div>');
                            $('#laso_napam .day').html('<div class="_napam pxg_geofesh_infox" data-type="nhat" data-info="napam">' + nhat_tru.napam.name + '</div>');
                            //laso_truongsinh
                            $('#laso_truongsinh .year').html('<div class="_thansat"><div class="pxg_bl pxg_geofesh_infox" data-type="nien" data-info="vongtruongsinh">' + thien_tru.vongtruongsinh.info.name + '</div></div>');
                            $('#laso_truongsinh .month').html('<div class="_thansat"><div class="pxg_bl pxg_geofesh_infox" data-type="nguyet" data-info="vongtruongsinh">' + nguyet_tru.vongtruongsinh.info.name + '</div></div>');
                            $('#laso_truongsinh .day').html('<div class="_thansat"><div class="pxg_bl pxg_geofesh_infox" data-type="nhat" data-info="vongtruongsinh">' + nhat_tru.vongtruongsinh.info.name + '</div></div>');
                        }
                        //ĐẠI VẬN
                        var tuoidaivan = _rest_objs.daivan.age;
                        var candaivan = _rest_objs.daivan.can;
                        var chidaivan = _rest_objs.daivan.chi;
                        var tangcan = _rest_objs.daivan.tangcan;
                        var chukydaivan = _rest_objs.daivan.circle_life;
                        //TUỔI + CAN + CHI + TÀNG CAN
                        var _htmlTuoi = '';
                        for (var _i = 0; _i < tuoidaivan.length; _i++) {
                            _htmlTuoi = _htmlTuoi + '<td colspan="3" style="width:100px">' + tuoidaivan[_i].age + ' (' + tuoidaivan[_i].year + ')</td>';
                        }
                        $('#tuoidaivan').html('<td class="head tddv">TUỔI</td>' + _htmlTuoi);
                        var _htmlDAIVAN = '';
                        for (var _i = 0; _i < 9; _i++) {
                            _htmlDAIVAN = _htmlDAIVAN + '<td colspan="3"><div class="pxg_daivan_thapthan"><span class="pxg_daivan_thapthan pxg_geo_des" data-type="thapthan" data-info="' + candaivan[_i].thapthan.name + '" title="' + candaivan[_i].thapthan.name + '">' + candaivan[_i].thapthan.key + '</span> | <span class="pxg_davan_can">' + candaivan[_i].can + '</span></div><div class="pxg_daivan_can_nguhanh">' + candaivan[_i].nguhanh + '</div><div class="pxg_daivan_chi">' + chidaivan[_i].chi + '</div><div class="pxg_daivan_nguhanh">' + chidaivan[_i].nguhanh + '</div></td>';
                        }
                        $('#canchidaivan').html('<td class="head">ĐẠI VẬN</td>' + _htmlDAIVAN);
                        //TÀNG CAN
                        var _htmlTANGCAN = '';
                        for (var _i = 0; _i < 9; _i++) {
                            _htmlTANGCAN = _htmlTANGCAN + '<td colspan="3">';
                            for (var _t = 0; _t < tangcan[_i].length; _t++) {
                                _htmlTANGCAN = _htmlTANGCAN + '<span class="pxg_tangcan_daivan"><div class="pxg_tangcang_daivan_thapthan pxg_geo_des" data-type="thapthan" data-info="' + tangcan[_i][_t].thapthan.name + '" title="' + tangcan[_i][_t].thapthan.name + '">' + tangcan[_i][_t].thapthan.key + '</div><div class="pxg_tangcan_daivan_can">' + tangcan[_i][_t].can + '</div><div class="pxg_tangcan_daivan_nguhanh">' + tangcan[_i][_t].nguhanh + '</div></span>';
                            }
                            _htmlTANGCAN = _htmlTANGCAN + '</td>';
                        }
                        $('#tangcandaivan').html('<td class="head">TÀNG CAN</td>' + _htmlTANGCAN);
                        //Nạp Âm
                        _napam_daivan = _rest_objs.daivan.napam;
                        var _htmlNAPAM_DAIVAN = '';
                        for (var _i = 0; _i < _napam_daivan.length; _i++) {
                            _htmlNAPAM_DAIVAN = _htmlNAPAM_DAIVAN + '<td colspan="3" class="normal _napam pxg_geofesh_infox" data-type="daivan" data-info="napam" data-id="' + _i + '">' + _napam_daivan[_i].name + '</td>';
                        }
                        if (_is_fee == false) {
                            $('#daivannapam').css('display', 'none');
                            $('#daivantruongsinh').css('display', 'none');
                            $('#daivanthansat').css('display', 'none');
                        } else {
                            $('#daivannapam').css('display', '');
                            $('#daivantruongsinh').css('display', '');
                            $('#daivanthansat').css('display', '');
                            $('#daivannapam').html('<td class="head">NẠP ÂM</td>' + _htmlNAPAM_DAIVAN);
                            //Trường Sinh
                            _truongsinh_daivan = _rest_objs.daivan.vongtruongsinh;
                            var _htmlTRUONGSINH_DAIVAN = '';
                            for (var _i = 0; _i < _truongsinh_daivan.length; _i++) {
                                _htmlTRUONGSINH_DAIVAN = _htmlTRUONGSINH_DAIVAN + '<td colspan="3" class="normal _napam pxg_geofesh_infox" data-type="daivan" data-info="vongtruongsinh" data-id="' + _i + '">' + _truongsinh_daivan[_i].info.name + '</td>';
                            }
                            $('#daivantruongsinh').html('<td class="head">TRƯỜNG SINH</td>' + _htmlTRUONGSINH_DAIVAN);
                            //Thần Sát
                            _thansat_daivan = _rest_objs.daivan.thansat;
                            var _htmlTHANSAT_DAIVAN = '';
                            for (var _i = 0; _i < _thansat_daivan.length; _i++) {
                                var _year_than_sat = '';
                                if (_thansat_daivan[_i] != undefined) {
                                    for (var _j = 0; _j < _thansat_daivan[_i].length; _j++) {
                                        _year_than_sat += '<div class="_thansat"><div class="pxg_bl pxg_geofesh_infox" data-type="daivan" data-info="thansat" data-id="' + _i + '" data-idx="' + _j + '">' + _thansat_daivan[_i][_j].name + '</div></div>';
                                    }
                                    _htmlTHANSAT_DAIVAN += '<td colspan="3" class="normal">' + _year_than_sat + '</td>';
                                }
                            }
                            $('#daivanthansat').html('<td class="head">THẦN SÁT</td>' + _htmlTHANSAT_DAIVAN);
                        }
                        //CHU KỲ
                        var _htmlCKDV = '';
                        _htmlCKDV += '<tr><td rowspan="11" class="tddv">10<br/>Năm<br/>Đại<br/>Vận</td></tr>';
                        for (var _r = 0; _r <= 9; _r++) {
                            _htmlCKDV += '<tr>';
                            for (var _y = 0; _y < 9; _y++) {
                                var _idx = (_y * 10) + _r;
                                _htmlCKDV += '<td' + ((_current_year == chukydaivan[_idx].year) ? ' class="active"' : '') + '><div class="pxg_geofesh_sdaivan_info cursor" data-year="' + chukydaivan[_idx].year + '" data-canchi="' + chukydaivan[_idx].can_chi + '">&nbsp;</div><span class="year">' + chukydaivan[_idx].year + '</span> ' + chukydaivan[_idx].can_chi + '</td>';
                            }
                            _htmlCKDV += '</tr>';
                        }
                        _htmlCKDV = '<table class="table-responsive"><tbody>' + _htmlCKDV + '</tbody></table>';
                        $('#chukydaivan').html('<div class="pxg_geo_head_chukydaivan">CHU KỲ ĐẠI VẬN - LƯU NIÊN 10 NĂM</div>' + _htmlCKDV);
                        if (_is_load == true) {
                            pxg_notice_remove_waiting('pxg_is_loading');
                        }
                    });
                }
            });
        </script>
        <?php
        return ob_get_clean();
    }
    /*Solar & Lunar Calendar*/
    public function INT($d)
    {
        return floor($d);
    }

    public function jdFromDate($dd, $mm, $yy)
    {
        $a = $this->INT((14 - $mm) / 12);
        $y = $yy + 4800 - $a;
        $m = $mm + 12 * $a - 3;
        $jd = $dd + $this->INT((153 * $m + 2) / 5) + 365 * $y + $this->INT($y / 4) - $this->INT($y / 100) + $this->INT($y / 400) - 32045;
        if ($jd < 2299161) {
            $jd = $dd + $this->INT((153 * $m + 2) / 5) + 365 * $y + $this->INT($y / 4) - 32083;
        }
        return $jd;
    }

    public function jdToDate($jd)
    {
        if ($jd > 2299160) { // After 5/10/1582, Gregorian calendar
            $a = $jd + 32044;
            $b = $this->INT((4 * $a + 3) / 146097);
            $c = $a - $this->INT(($b * 146097) / 4);
        } else {
            $b = 0;
            $c = $jd + 32082;
        }
        $d = $this->INT((4 * $c + 3) / 1461);
        $e = $c - $this->INT((1461 * $d) / 4);
        $m = $this->INT((5 * $e + 2) / 153);
        $day = $e - $this->INT((153 * $m + 2) / 5) + 1;
        $month = $m + 3 - 12 * $this->INT($m / 10);
        $year = $b * 100 + $d - 4800 + $this->INT($m / 10);
        //echo "day = $day, month = $month, year = $year\n";
        return array($day, $month, $year);
    }

    public function getNewMoonDay($k, $timeZone)
    {
        $T = $k / 1236.85; // Time in Julian centuries from 1900 January 0.5
        $T2 = $T * $T;
        $T3 = $T2 * $T;
        $dr = M_PI / 180;
        $Jd1 = 2415020.75933 + 29.53058868 * $k + 0.0001178 * $T2 - 0.000000155 * $T3;
        $Jd1 = $Jd1 + 0.00033 * sin((166.56 + 132.87 * $T - 0.009173 * $T2) * $dr); // Mean new moon
        $M = 359.2242 + 29.10535608 * $k - 0.0000333 * $T2 - 0.00000347 * $T3; // Sun's mean anomaly
        $Mpr = 306.0253 + 385.81691806 * $k + 0.0107306 * $T2 + 0.00001236 * $T3; // Moon's mean anomaly
        $F = 21.2964 + 390.67050646 * $k - 0.0016528 * $T2 - 0.00000239 * $T3; // Moon's argument of latitude
        $C1 = (0.1734 - 0.000393 * $T) * sin($M * $dr) + 0.0021 * sin(2 * $dr * $M);
        $C1 = $C1 - 0.4068 * sin($Mpr * $dr) + 0.0161 * sin($dr * 2 * $Mpr);
        $C1 = $C1 - 0.0004 * sin($dr * 3 * $Mpr);
        $C1 = $C1 + 0.0104 * sin($dr * 2 * $F) - 0.0051 * sin($dr * ($M + $Mpr));
        $C1 = $C1 - 0.0074 * sin($dr * ($M - $Mpr)) + 0.0004 * sin($dr * (2 * $F + $M));
        $C1 = $C1 - 0.0004 * sin($dr * (2 * $F - $M)) - 0.0006 * sin($dr * (2 * $F + $Mpr));
        $C1 = $C1 + 0.0010 * sin($dr * (2 * $F - $Mpr)) + 0.0005 * sin($dr * (2 * $Mpr + $M));
        if ($T < -11) {
            $deltat = 0.001 + 0.000839 * $T + 0.0002261 * $T2 - 0.00000845 * $T3 - 0.000000081 * $T * $T3;
        } else {
            $deltat = -0.000278 + 0.000265 * $T + 0.000262 * $T2;
        };
        $JdNew = $Jd1 + $C1 - $deltat;
        //echo "JdNew = $JdNew\n";
        return $this->INT($JdNew + 0.5 + $timeZone / 24);
    }

    public function getSunLongitude($jdn, $timeZone)
    {
        $T = ($jdn - 2451545.5 - $timeZone / 24) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
        $T2 = $T * $T;
        $dr = M_PI / 180; // degree to radian
        $M = 357.52910 + 35999.05030 * $T - 0.0001559 * $T2 - 0.00000048 * $T * $T2; // mean anomaly, degree
        $L0 = 280.46645 + 36000.76983 * $T + 0.0003032 * $T2; // mean longitude, degree
        $DL = (1.914600 - 0.004817 * $T - 0.000014 * $T2) * sin($dr * $M);
        $DL = $DL + (0.019993 - 0.000101 * $T) * sin($dr * 2 * $M) + 0.000290 * sin($dr * 3 * $M);
        $L = $L0 + $DL; // true longitude, degree
        //echo "\ndr = $dr, M = $M, T = $T, DL = $DL, L = $L, L0 = $L0\n";
        // obtain apparent longitude by correcting for nutation and aberration
        $omega = 125.04 - 1934.136 * $T;
        $L = $L - 0.00569 - 0.00478 * sin($omega * $dr); //Math.sin($omega * $dr)
        $L = $L * $dr;
        $L = $L - M_PI * 2 * ($this->INT($L / (M_PI * 2))); // Normalize to (0, 2*PI)
        return $this->INT($L / M_PI * 6);
    }

    public function SunLongitude($jdn)
    {
        $T = ($jdn - 2451545.0) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
        $T2 = $T * $T;
        $dr = M_PI / 180; // degree to radian
        $M = 357.52910 + 35999.05030 * $T - 0.0001559 * $T2 - 0.00000048 * $T * $T2; // mean anomaly, degree
        $L0 = 280.46645 + 36000.76983 * $T + 0.0003032 * $T2; // mean longitude, degree
        $DL = (1.914600 - 0.004817 * $T - 0.000014 * $T2) * sin($dr * $M);
        $DL = $DL + (0.019993 - 0.000101 * $T) * sin($dr * 2 * $M) + 0.000290 * sin($dr * 3 * $M);
        $theta = $L0 + $DL; // true longitude, degree
        // obtain apparent longitude by correcting for nutation and aberration
        $omega = 125.04 - 1934.136 * $T;
        $lambda = $theta - 0.00569 - 0.00478 * sin($omega * $dr);
        // Convert to radians
        $lambda = $lambda * $dr;
        $lambda = $lambda - M_PI * 2 * ($this->INT($lambda / (M_PI * 2))); // Normalize to (0, 2*PI)
        return $lambda;
    }
    public function getSolarTerm($dayNumber, $timeZone)
    {
        return $this->INT($this->SunLongitude($dayNumber - 0.5 - $timeZone / 24.0) / M_PI * 12);
    }
    public function getLunarMonth11($yy, $timeZone)
    {
        $off = $this->jdFromDate(31, 12, $yy) - 2415021;
        $k = $this->INT($off / 29.530588853);
        $nm = $this->getNewMoonDay($k, $timeZone);
        $sunLong = $this->getSunLongitude($nm, $timeZone); // sun longitude at local midnight
        if ($sunLong >= 9) {
            $nm = $this->getNewMoonDay($k - 1, $timeZone);
        }
        return $nm;
    }

    public function getLeapMonthOffset($a11, $timeZone)
    {
        $k = $this->INT(($a11 - 2415021.076998695) / 29.530588853 + 0.5);
        $last = 0;
        $i = 1; // We start with the month following lunar month 11
        $arc = $this->getSunLongitude($this->getNewMoonDay($k + $i, $timeZone), $timeZone);
        do {
            $last = $arc;
            $i = $i + 1;
            $arc = $this->getSunLongitude($this->getNewMoonDay($k + $i, $timeZone), $timeZone);
        } while ($arc != $last && $i < 14);
        return $i - 1;
    }

    /* Comvert solar date dd/mm/yyyy to the corresponding lunar date */
    public function pxg_get_lunar_year_is_leap($yearLunar)
    {/*Check this funtions by times: leap by lunar*/
        $l = $yearLunar % 19;
        if ($l == 3 || $l == 6 || $l == 9 || $l == 11 || $l == 14 || $l == 17) { //Nhuận;
            return 1;
        }
        return 0; //Không Nhuận;
    }
    public function pxg_get_lunar_leap_from_solar($dd, $mm, $yy, $timeZone)
    {
        $dayNumber = $this->jdFromDate($dd, $mm, $yy);
        $k = $this->INT(($dayNumber - 2415021.076998695) / 29.530588853);
        $monthStart = $this->getNewMoonDay($k + 1, $timeZone);
        if ($monthStart > $dayNumber) {
            $monthStart = $this->getNewMoonDay($k, $timeZone);
        }
        $a11 = $this->getLunarMonth11($yy, $timeZone);
        $b11 = $a11;
        if ($a11 >= $monthStart) {
            $a11 = $this->getLunarMonth11($yy - 1, $timeZone);
        } else {
            $b11 = $this->getLunarMonth11($yy + 1, $timeZone);
        }
        $diff = $this->INT(($monthStart - $a11) / 29);
        $lunarLeap = 0;
        $lunarMonth = $diff + 11;
        if ($b11 - $a11 > 365) {
            $leapMonthDiff = $this->getLeapMonthOffset($a11, $timeZone);
            if ($diff >= $leapMonthDiff) {
                $lunarMonth = $diff + 10;
                if ($diff == $leapMonthDiff) {
                    $lunarLeap = 1;
                }
            }
        }
        return $lunarLeap;
    }
    public function convertSolar2Lunar($dd, $mm, $yy, $timeZone)
    {
        $dayNumber = $this->jdFromDate($dd, $mm, $yy);
        $k = $this->INT(($dayNumber - 2415021.076998695) / 29.530588853);
        $monthStart = $this->getNewMoonDay($k + 1, $timeZone);
        if ($monthStart > $dayNumber) {
            $monthStart = $this->getNewMoonDay($k, $timeZone);
        }
        $a11 = $this->getLunarMonth11($yy, $timeZone);
        $b11 = $a11;
        if ($a11 >= $monthStart) {
            $lunarYear = $yy;
            $a11 = $this->getLunarMonth11($yy - 1, $timeZone);
        } else {
            $lunarYear = $yy + 1;
            $b11 = $this->getLunarMonth11($yy + 1, $timeZone);
        }
        $lunarDay = $dayNumber - $monthStart + 1;
        $diff = $this->INT(($monthStart - $a11) / 29);
        $lunarLeap = 0;
        $lunarMonth = $diff + 11;
        if ($b11 - $a11 > 365) {
            $leapMonthDiff = $this->getLeapMonthOffset($a11, $timeZone);
            if ($diff >= $leapMonthDiff) {
                $lunarMonth = $diff + 10;
                if ($diff == $leapMonthDiff) {
                    $lunarLeap = 1;
                }
            }
        }
        if ($lunarMonth > 12) {
            $lunarMonth = $lunarMonth - 12;
        }
        if ($lunarMonth >= 11 && $diff < 4) {
            $lunarYear -= 1;
        }
        return array($lunarDay, $lunarMonth, $lunarYear, $lunarLeap);
    }

    /* Convert a lunar date to the corresponding solar date */
    public function convertLunar2Solar($lunarDay, $lunarMonth, $lunarYear, $lunarLeap, $timeZone)
    {
        if ($lunarMonth < 11) {
            $a11 = $this->getLunarMonth11($lunarYear - 1, $timeZone);
            $b11 = $this->getLunarMonth11($lunarYear, $timeZone);
        } else {
            $a11 = $this->getLunarMonth11($lunarYear, $timeZone);
            $b11 = $this->getLunarMonth11($lunarYear + 1, $timeZone);
        }
        $k = $this->INT(0.5 + ($a11 - 2415021.076998695) / 29.530588853);
        $off = $lunarMonth - 11;
        if ($off < 0) {
            $off += 12;
        }
        if ($b11 - $a11 > 365) {
            $leapOff = $this->getLeapMonthOffset($a11, $timeZone);
            $leapMonth = $leapOff - 2;
            if ($leapMonth < 0) {
                $leapMonth += 12;
            }
            if ($lunarLeap != 0 && $lunarMonth != $leapMonth) {
                return array(0, 0, 0);
            } else if ($lunarLeap != 0 || $off >= $leapOff) {
                $off += 1;
            }
        }
        $monthStart = $this->getNewMoonDay($k + $off, $timeZone);
        return $this->jdToDate($monthStart + $lunarDay - 1);
    }
    public function getMonthCanChi($mm, $yy)
    {
        return self::$CAN[($yy * 12 + $mm + 3) % 10] . " " . self::$CHI[($mm + 1) % 12];
    }
    public function getMonthChi($mm)
    {
        return self::$CHI[($mm + 1) % 12];
    }
    public function getMonthCan($mm, $yy)
    {
        return self::$CAN[($yy * 12 + $mm + 3) % 10];
    }

    public function getDayCan($jd)
    {
        return self::$CAN[($jd + 9) % 10];
    }
    public function getDayChi($jd)
    {
        return self::$CHI[($jd + 1) % 12];
    }
    public function getDayCanChi($jd)
    {
        return self::$CAN[($jd + 9) % 10] . " " . self::$CHI[($jd + 1) % 12];
    }
    public function getTietKhi($jd)
    {
        return self::$TIETKHI[$this->getSolarTerm($jd + 1, 7.0)];
    }
    //http://www.informatik.uni-leipzig.de/~duc/amlich/
    public function pxg_get_jd_from_date($dd, $mm, $yy, $hour, $minutes)
    {
        $ret = $this->jdn($dd, $mm, $yy);
        return $ret - 0.5 + ($hour - 7) / 24.0 + $minutes / 1440.0;
    }
    public function solarLongitude($jd)
    {
        $T = ($jd - 2451545.0) / 36525; // Time in Julian centuries from 2000-01-01 12:00:00 GMT
        $T2 = $T * $T;
        $dr = M_PI / 180; // degree to radian
        // mean anomaly, degree
        $M = 357.52910 + 35999.05030 * $T - 0.0001559 * $T2 - 0.00000048 * $T * $T2;
        // mean longitude, degree
        $L0 = 280.46645 + 36000.76983 * $T + 0.0003032 * $T2;
        // Sun's equation of center
        $C = (1.914600 - 0.004817 * $T - 0.000014 * $T2) * sin($dr * $M);
        $C = $C + (0.019993 - 0.000101 * $T) * sin($dr * 2 * $M) + 0.000290 * sin($dr * 3 * $M);
        $theta = $L0 + $C; // true longitude, degree
        // obtain apparent longitude by correcting for nutation and aberration
        $omega = 125.04 - 1934.136 * $T;
        $lambda = $theta - 0.00569 - 0.00478 * sin($omega * $dr);
        // Normalize to (0, 360)
        $lambda = $lambda - 360 * ($this->INT($lambda / 360)); // Normalize to (0, 2*PI)
        return $lambda;
    }
    public function get_TietKhi_by_time($dd, $mm, $yy, $hh, $mi, $ss = 0)
    {
        $ld = $this->getLunarDate($dd, $mm, $yy);
        $_tietKhi = $this->getTietKhi($ld->jd);
        $_tietKhiRest = $_tietKhi;
        $jd = $this->pxg_get_jd_from_date($dd, $mm, $yy, $hh, $mi);
        $cur_degree = $this->solarLongitude($jd);
        $idx_tietKhi_by_name = $this->pxg_get_id_arr(self::$TIETKHI, $_tietKhi);
        $degree_tietKhi = self::$TIET_KHI_DEGREE[$idx_tietKhi_by_name];
        if ($cur_degree >= 345) {
            $_tietKhiRest = self::$TIETKHI[count(self::$TIETKHI) - 1]; //Kinh Trập;
        } else {
            //284.98947432026 < 285
            if ($cur_degree >= $degree_tietKhi) {
                $_tietKhiRest = self::$TIETKHI[$idx_tietKhi_by_name];
            } else if ($cur_degree < $degree_tietKhi) {
                $_tietKhiRest = self::$TIETKHI[$idx_tietKhi_by_name - 1];
            }
        }
        return $_tietKhiRest; //.' => '.$cur_degree.' - '.$degree_tietKhi;
    }
    public function getYearCan($year)
    {
        return self::$CAN[($year + 6) % 10];
    }
    public function getYearChi($year)
    {
        return self::$CHI[($year + 8) % 12];
    }
    public function getYearCanChi($year)
    {
        $year = intval($year);
        return self::$CAN[($year + 6) % 10] . " " . self::$CHI[($year + 8) % 12];
    }
    public function getCanHour0($jdn)
    {
        return self::$CAN[($jdn - 1) * 2 % 10];
    }
    public function getCanHour($dd, $mm, $yy, $hh)
    {
        $_chi_gio = $this->getChiHour($hh);
        $ld = $this->getLunarDate($dd, $mm, $yy);
        $can_0_gio = $this->getCanHour0($ld->jd);/*Lấy mốc là CAN lúc 0H*/
        //var_dump($can_0_gio);
        //self::$CAN = ["Giáp","Ất","Bính","Đinh","Mậu","Kỷ","Canh","Tân","Nhâm","Quý"];
        $idx_can_0_gio = self::pxg_get_id_arr(self::$CAN, $can_0_gio);
        //var_dump($idx_can_0_gio);
        $idx_chi_gio = self::pxg_get_id_arr(self::$CHI, $_chi_gio) + (!empty($idx_can_0_gio) ? $idx_can_0_gio : 0);
        if ($idx_chi_gio < 10) {
            return self::$CAN[$idx_chi_gio];
        }
        return self::$CAN[(($idx_chi_gio - 10) % 10)];
    }
    public function getChiHour($_hour)
    {
        if ($_hour >= 23 || $_hour < 1) {
            $_chi_gio = 'Tý';
        } else if ($_hour >= 1 && $_hour < 3) {
            $_chi_gio = 'Sửu';
        } else if ($_hour >= 3 && $_hour < 5) {
            $_chi_gio = 'Dần';
        } else if ($_hour >= 5 && $_hour < 7) {
            $_chi_gio = 'Mão';
        } else if ($_hour >= 7 && $_hour < 9) {
            $_chi_gio = 'Thìn';
        } else if ($_hour >= 9 && $_hour < 11) {
            $_chi_gio = 'Tỵ';
        } else if ($_hour >= 11 && $_hour < 13) {
            $_chi_gio = 'Ngọ';
        } else if ($_hour >= 13 && $_hour < 15) {
            $_chi_gio = 'Mùi';
        } else if ($_hour >= 15 && $_hour < 17) {
            $_chi_gio = 'Thân';
        } else if ($_hour >= 17 && $_hour < 19) {
            $_chi_gio = 'Dậu';
        } else if ($_hour >= 19 && $_hour < 21) {
            $_chi_gio = 'Tuất';
        } else if ($_hour >= 21 && $_hour < 23) {
            $_chi_gio = 'Hợi';
        }
        return $_chi_gio;
    }
    public function getHourByChi($_chiHour)
    {
        switch ($_chiHour) {
            case 'Tý':
                return 23;
            case 'Sửu':
                return 1;
            case 'Dần':
                return 3;
            case 'Mão':
                return 5;
            case 'Thìn':
                return 7;
            case 'Tỵ':
                return 9;
            case 'Ngọ':
                return 11;
            case 'Mùi':
                return 13;
            case 'Thân':
                return 15;
            case 'Dậu':
                return 17;
            case 'Tuất':
                return 19;
            case 'Hợi':
                return 21;
            default:
                return 23;/*Tý*/
        }
        return 23;/*Tý*/
    }
    public function getGioHoangDaoTuGioHienTai($jd, $hh)
    {
        $chiOfDay = ($jd + 1) % 12;
        $gioHD = self::$GIO_HD[$chiOfDay % 6];
        $ret = array();
        $count = 0;
        $_hour = intval($hh) - 0;
        for ($i = 0; $i < 12; $i++) {
            if ($gioHD[$i] == '1') {
                $_h1 = ($i * 2 + 23) % 24;
                $_h2 = ($i * 2 + 1) % 24;
                if ($_h1 >= $_hour && $_hour <= $_h2) {
                    array_push($ret, self::$CHI[$i] . ' (' . $_h1 . '-' . $_h2 . ')');
                }
                //if ($count++ < 5) $ret .= ', ';
            }
        }
        return implode(', ', $ret);
    }
    public function getGioHoangDaoKietHung($jd)
    {
        $dayChi = $this->getDayChi($jd);
        $chiOfDay = ($jd + 1) % 12;
        $gioHD = self::$GIO_HD[$chiOfDay % 6];
        $ret = array();
        $count = 0;
        for ($i = 0; $i < 12; $i++) {
            if ($gioHD[$i] == '1') {
                $_h1 = ($i * 2 + 23) % 24;
                $_h2 = ($i * 2 + 1) % 24;
                $data_kiet_hung = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->hoangdaohacdao, $this->pxg_get_KIETHUNGTHOI($dayChi, self::$CHI[$i]));
                array_push($ret, (object)array('chi' => self::$CHI[$i], 'hour' => $_h1 . '-' . $_h2, 'kiethung' => $data_kiet_hung));
            }
        }
        return $ret;
    }
    public function getGioHoangDao($jd)
    {
        $chiOfDay = ($jd + 1) % 12;
        $gioHD = self::$GIO_HD[$chiOfDay % 6];
        $ret = array();
        $count = 0;
        for ($i = 0; $i < 12; $i++) {
            if ($gioHD[$i] == '1') {
                $_h1 = ($i * 2 + 23) % 24;
                $_h2 = ($i * 2 + 1) % 24;
                array_push($ret, (object)array('chi' => self::$CHI[$i], 'hour' => $_h1 . '-' . $_h2));
            }
        }
        return $ret;
    }
    /*Solar & Lunar Calendar*/
    /*Ngày tốt xấu*/

    public function pxg_geofesh_date($agr)
    {
        extract(shortcode_atts(
            array(
                'day' => '',
                'month' => '',
                'year' => '',
                'hour' => '',
                'minute' => '',
                'is_text' => ''
            ),
            $agr
        ));

        ob_start();
        //$this->pxg_geofesh_date_call($agr);//TODO TESTX
        if (isset(self::$pxg_geofesh_data->datetime_border_color) && self::$pxg_geofesh_data->datetime_border_color != '') {
        ?>
            <style>
                .pxg_gf_dgb_bl {
                    background: <?php echo self::$pxg_geofesh_data->datetime_border_color ?>
                }

                .pxg_gf_dgb_bl .hd {
                    background: <?php echo self::$pxg_geofesh_data->datetime_border_color ?>
                }

                .pxg_gf_dgb_bl .wrap {
                    background: <?php echo self::$pxg_geofesh_data->datetime_background_color ?>
                }
            </style>
        <?php
        }
        ?>
        <div class="pxg_geofesh_date" id="pxg_geofesh_date" data-logo="<?php echo self::$pxg_geofesh_data->logo_link ?>" data-message="Đang tính Ngày Tốt Xấu">
            <div class="row col-lg-12 nopad">
                <div class="col-lg-4">
                    <div class="pxg_gf_dgb_bl" id="normal">
                        <div class="bg">
                            <div class="hd">TỔNG QUAN</div>
                            <div class="wrap">
                                <div class="row">
                                    <div class="col-lg-5 col-5">Giờ đang xem:</div>
                                    <div class="col-lg-7 col-7 bb hour pxg_geo_date_des"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Nạp Âm Ngày:</div>
                                    <div class="col-lg-7 col-7 bb napam pxg_geo_date_des"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Thập Nhị Trực:</div>
                                    <div class="col-lg-7 col-7 bb thapnhitruc pxg_geo_date_des"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Kiết Hung Nhật:</div>
                                    <div class="col-lg-7 col-7 bb kiethungnhat pxg_geo_date_des"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Đại Tiểu Nguyệt:</div>
                                    <div class="col-lg-7 col-7 bb daitieunguyet pxg_geo_date_des"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Nhị Thập Bát Tú:</div>
                                    <div class="col-lg-7 col-7 bb nhithapbattu pxg_geo_date_des"></div>
                                </div>
                                <!--<div class="row"><div class="col-lg-5 col-5">Ngày Âm Dương:</div><div class="col-lg-7 bb amduongnhat"></div></div>-->
                                <div class="row">
                                    <div class="col-lg-5 col-5">Thập Nhị Can Ngày:</div>
                                    <div class="col-lg-7 col-7 bb thapnhicannhat pxg_geo_date_des"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Thập Nhị Chi Ngày:</div>
                                    <div class="col-lg-7 col-7 bb thapnhichinhat pxg_geo_date_des"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="pxg_gf_dgb_bl" id="pxg_get_date_good_bad" data-date="<?php echo self::$pxg_geofesh_data->geofesh_date_slug ?>" data-rule="<?php echo self::$pxg_geofesh_data->geofesh_rewrite_date_rule ?>">
                        <div class="bg">
                            <div class="hd">XEM NGÀY TỐT, XẤU</div>
                            <div class="wrap">
                                <div>
                                    <div style="text-align: center; text-transform: uppercase; font-weight: 700; font-size: 16px;">Đang xem <span id="pxg_curday_name"></span></div>
                                    <div class="row text-center date_solar_lunar_in">
                                        <div class="col-6">
                                            <div class="cal_head">Dương Lịch</div>
                                            <input class="showsolar col-12 col-lg-12 text-center" type="text" value="1/2/2002" />
                                        </div>
                                        <div class="col-6">
                                            <div class="cal_head">Âm Lịch</div>
                                            <input class="showlunar col-12 col-lg-12 text-center" type="text" />
                                        </div>
                                    </div>
                                    <div class="row text-center">
                                        <div class="col-lg-3 col-3"><b>Năm</b>
                                            <div class="cyear"></div>
                                        </div>
                                        <div class="col-lg-3 col-3"><b>Tháng</b>
                                            <div class="cmonth"></div>
                                        </div>
                                        <div class="col-lg-3 col-3"><b>Ngày</b>
                                            <div class="cday"></div>
                                        </div>
                                        <div class="col-lg-3 col-3"><b>Giờ</b>
                                            <div class="chour"></div>
                                        </div>
                                    </div>
                                    <div class="btn_view_date cursor text-center geofesh_date_expand"><span class="geofesh_today_back">
                                            <<< /span> <span class="geofesh_today">XEM HÔM NAY</span> <span class="geofesh_today_next">>></span></div>
                                    <div class="pxg_geo_notice text-center col-12"><strong>Lưu ý:</strong> để hiểu rõ về các thông tin mô tả chi tiết của thuật ngữ, bạn vui lòng nhấn vào tên thuật ngữ!</div>
                                </div>
                                <!-- Modal -->
                                <div class="modal fade" id="pxg_lunar_solar_date" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content">
                                            <div class="cal_header">
                                                <button type="button" class="close cal_closed" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body content">
                                                <div id="pxg_geofesh_date_choose_date">
                                                    <!--cal-->
                                                    <div class="row text-center">
                                                        <div class="col-6 cal_head">Dương Lịch</div>
                                                        <div class="col-6 cal_head">Âm Lịch</div>
                                                    </div>
                                                    <div class="row date_solar_lunar_in">
                                                        <div class="col-6">
                                                            <div class="pxg_block_100">
                                                                <input type="number" style="width: 25%; text-align: center;" class="day_solar" /><input type="number" style="width: 25%; text-align: center;" class="month_solar" />
                                                                <input type="number" style="width: 50%; text-align: center;" class="year_solar" />
                                                            </div>
                                                            <div style="margin-top: 1px;">
                                                                <select id="hour_solar" class="hour_solar cursor">
                                                                    <option value="Tý">
                                                                        23:00 ~ 00:59
                                                                    </option>
                                                                    <option value="Sửu">
                                                                        01:00 ~ 02:59
                                                                    </option>
                                                                    <option value="Dần">
                                                                        03:00 ~ 04:59
                                                                    </option>
                                                                    <option value="Mão">
                                                                        05:00 ~ 06:59
                                                                    </option>
                                                                    <option value="Thìn">
                                                                        07:00 ~ 08:59
                                                                    </option>
                                                                    <option value="Tỵ">
                                                                        09:00 ~ 10:59
                                                                    </option>
                                                                    <option value="Ngọ">
                                                                        11:00 ~ 12:59
                                                                    </option>
                                                                    <option value="Mùi">
                                                                        13:00 ~ 14:59
                                                                    </option>
                                                                    <option value="Thân">
                                                                        15:00 ~ 16:59
                                                                    </option>
                                                                    <option value="Dậu">
                                                                        17:00 ~ 18:59
                                                                    </option>
                                                                    <option value="Tuất">
                                                                        19:00 ~ 20:59
                                                                    </option>
                                                                    <option value="Hợi">
                                                                        21:00 ~ 22:59
                                                                    </option>
                                                                </select>
                                                            </div>
                                                            <div style="margin-top: 1px;">
                                                                <span id="pxg_view_solar" class="btn btn-primary btn_view_date">XEM</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <div class="pxg_block_100">
                                                                <input type="number" style="width: 25%; text-align: center;" class="day_lunar" /><input type="number" style="width: 25%; text-align: center;" class="month_lunar" />
                                                                <input type="number" style="width: 50%; text-align: center;" class="year_lunar" />
                                                            </div>
                                                            <div style="margin-top: 1px;">
                                                                <select id="hour_lunar" class="hour_lunar cursor">
                                                                    <option value="Tý"> Giờ Tý </option>
                                                                    <option value="Sửu"> Giờ Sửu </option>
                                                                    <option value="Dần"> Giờ Dần </option>
                                                                    <option value="Mão"> Giờ Mão </option>
                                                                    <option value="Thìn"> Giờ Thìn </option>
                                                                    <option value="Tỵ"> Giờ Tỵ </option>
                                                                    <option value="Ngọ"> Giờ Ngọ </option>
                                                                    <option value="Mùi"> Giờ Mùi </option>
                                                                    <option value="Thân"> Giờ Thân </option>
                                                                    <option value="Dậu"> Giờ Dậu </option>
                                                                    <option value="Tuất"> Giờ Tuất </option>
                                                                    <option value="Hợi"> Giờ Hợi </option>
                                                                </select>
                                                            </div>
                                                            <div style="margin-top: 1px;">
                                                                <span id="pxg_view_lunar" class="btn btn-primary btn_view_date">XEM</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div id="pxg_geo_calendar"></div>
                                                </div>
                                                <!--//cal-->
                                                <div class="col-12 text-center cal_footer">
                                                    <button type="button" class="btn btn-secondary" data-dismiss="modal">- ĐÓNG LỊCH -</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- //Modal -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4">
                    <div class="pxg_gf_dgb_bl" id="cuucungphitinh">
                        <div class="bg">
                            <div class="hd">THÔNG TIN</div>
                            <div class="wrap">
                                <div class="row">
                                    <div class="col-lg-5 col-5">Năm Cửu Tinh:</div>
                                    <div class="col-lg-7 col-7 bb year cursor pxg_geo_date_des_9star"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Tháng Cửu Tinh:</div>
                                    <div class="col-lg-7 col-7 bb month cursor pxg_geo_date_des_9star"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Ngày Cửu Tinh:</div>
                                    <div class="col-lg-7 col-7 bb day cursor pxg_geo_date_des_9star"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Giờ Cửu Tinh:</div>
                                    <div class="col-lg-7 col-7 bb hour cursor pxg_geo_date_des_9star"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Ngày Lục Nhâm:</div>
                                    <div class="col-lg-7 col-7 bb daylucnham cursor pxg_geo_date_des"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Nhị Thập Tứ Khí:</div>
                                    <div class="col-lg-7 col-7 bb nhithaptukhi cursor pxg_geo_date_des"></div>
                                </div>
                                <div class="row">
                                    <div class="col-lg-5 col-5">Ngày Đầu Tháng:</div>
                                    <div class="col-lg-7 col-7 bb dayccdauthang cursor"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-12 nopad">
                <!--<div class="col-lg-3 col-6">
                <div class="pxg_gf_dgb_bl">
                    <div class="bg">
                        <div class="hd">KIẾT TINH</div>
                        <div class="wrap">
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="pxg_gf_dgb_bl">
                    <div class="bg">
                        <div class="hd">HUNG TINH</div>
                        <div class="wrap">
                        </div>
                    </div>
                </div>
            </div>-->
                <div class="col-lg-3 col-6">
                    <div class="pxg_gf_dgb_bl" id='age_conflict_day'>
                        <div class="bg">
                            <div class="hd">TUỔI XUNG KHẮC VỚI NGÀY</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="pxg_gf_dgb_bl" id='age_conflict_month'>
                        <div class="bg">
                            <div class="hd">TUỔI XUNG KHẮC VỚI THÁNG</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="pxg_gf_dgb_bl" id="huongkiethung">
                        <div class="bg">
                            <div class="hd">HƯỚNG KIẾT HUNG</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="pxg_gf_dgb_bl" id="hourkhongvong">
                        <div class="bg">
                            <div class="hd">GIỜ KHÔNG VONG</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-12 nopad">
                <div class="col-lg-3 col-6">
                    <div class="pxg_gf_dgb_bl" id="tamsatnien">
                        <div class="bg">
                            <div class="hd">TAM SÁT NIÊN</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="pxg_gf_dgb_bl" id="tamsatnguyet">
                        <div class="bg">
                            <div class="hd">TAM SÁT NGUYỆT</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="pxg_gf_dgb_bl" id="tamsatnhat">
                        <div class="bg">
                            <div class="hd">TAM SÁT NHỰT</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="pxg_gf_dgb_bl" id="tamsatthoi">
                        <div class="bg">
                            <div class="hd">TAM SÁT THỜI</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-12 nopad">
                <div class="col-lg-4 col-12 col-md-4 col-sm-4">
                    <div class="pxg_gf_dgb_bl" id="hour_hoang_dao">
                        <div class="bg">
                            <div class="hd">GIỜ HOÀNG ĐẠO</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12 col-md-4 col-sm-4" id="hour_stars">
                    <div class="pxg_gf_dgb_bl">
                        <div class="bg">
                            <div class="hd">SAO THEO GIỜ</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12 col-md-4 col-sm-4">
                    <div class="pxg_gf_dgb_bl" id="hour_luc_nham">
                        <div class="bg">
                            <div class="hd">GIỜ LỤC NHÂM</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row col-12 nopad">
                <div class="col-lg-4 col-12 col-md-4 col-sm-4">
                    <div class="pxg_gf_dgb_bl" id="DongCong">
                        <div class="bg">
                            <div class="hd">ĐỔNG CÔNG – SOẠN TRẠCH NHẬT</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12 col-md-4 col-sm-4">
                    <div class="pxg_gf_dgb_bl" id="day_tuepha">
                        <div class="bg">
                            <div class="hd">TUẾ PHÁ</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-12 col-md-4 col-sm-4">
                    <div class="pxg_gf_dgb_bl" id="day_tuly_tutuyet">
                        <div class="bg">
                            <div class="hd">TỨ LY & TỨ TUYỆT</div>
                            <div class="wrap content">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
        $this->pxg_geofesh_generate_popup('pxg_geofesh_date_compass');
        return ob_get_clean();
    }
    //TODO: functions

    public function pxg_geofesh_date_call($args)
    {
        \date_default_timezone_set('Asia/Ho_Chi_Minh');
        $timezone = 7.0;
        $_daySolar = isset($_POST['day']) ? $_POST['day'] : (isset($args['day']) ? $args['day'] : date('d'));
        $_monthSolar = isset($_POST['month']) ? $_POST['month'] : (isset($args['month']) ? $args['month'] : date('m'));/*from 0-11*/
        $_yearSolar = isset($_POST['year']) ? $_POST['year'] : (isset($args['year']) ? $args['year'] : date('Y'));
        $_hourChiLunar = isset($_POST['hour']) ? $_POST['hour'] : (isset($args['hour']) ? $args['hour'] : 'Tý');
        $_calendar = isset($_POST['calendar']) ? $_POST['calendar'] : (isset($args['calendar']) ? $args['calendar'] : 'solar');
        $_monthNoName = 0;
        if (!empty($_daySolar)) {
            $_daySolar = intval($_daySolar);
        }
        if (!empty($_monthSolar)) {
            $_monthSolar = intval($_monthSolar);
        }
        if (!empty($_yearSolar)) {
            $_yearSolar = intval($_yearSolar);
        }
        $lunarLeapCurrentYear = $this->pxg_get_lunar_leap_from_solar(date('d'), date('m'), date('Y'), $timezone);
        if ($_calendar === 'lunar') {
            $_dateSolar = $this->convertLunar2Solar($_daySolar, $_monthSolar, $_yearSolar, $lunarLeapCurrentYear, $timezone);
            $_daySolar = $_dateSolar[0];
            $_monthSolar = $_dateSolar[1];
            $_yearSolar = $_dateSolar[2];
        }
        $_tr = '';
        $ld = $this->getLunarDate($_daySolar, $_monthSolar, $_yearSolar);
        $_current_hour = $this->getHourByChi($_hourChiLunar); //date('H');
        $_current_minute = date('i');

        $dayChi = $this->getDayChi($ld->jd);
        $dayCan = $this->getDayCan($ld->jd);
        $dayCanChi = $this->getDayCanChi($ld->jd);

        //First day by Month: convert lunar to Solar => Solar => Lunar get ->jnd => getDayCanChi
        $_date = $this->convertLunar2Solar(1, $ld->month, $ld->year, $lunarLeapCurrentYear, $timezone);
        $prevousLunar = $this->getLunarDate($_date[0], $_date[1], $_date[2]);
        $firstDayCanChiMonth = $this->getDayCanChi($prevousLunar->jd);
        //First day by Month: convert lunar to Solar => Solar => Lunar get ->jnd => getDayCanChi

        $dayCanChiDUONGLICH = $this->getDayCanChi($this->jdn($_daySolar, $_monthSolar, $_yearSolar));
        $monthCan = $this->getMonthCan($ld->month, $ld->year);
        $monthChi = $this->getMonthChi($ld->month);
        $monthCanChi = $this->getMonthCanChi($ld->month, $ld->year);
        $monthCanChiDUONGLICH = $this->getMonthCanChi($_monthSolar, $_yearSolar);

        $yearCan = $this->getYearCan($ld->year);
        $yearChi = $this->getYearChi($ld->year);
        $yearCanChi = $this->getYearCanChi($ld->year);
        $yearCanChiDUONGLICH = $this->getYearCanChi($_yearSolar);

        $_tietKhi = $this->getTietKhi($ld->jd);

        $tietkhi_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->tietkhi, $_tietKhi);

        $yearNguHanh = $this->pxg_get_ngu_hanh_by_chi($yearChi);
        $dayNguHanh = $this->pxg_get_ngu_hanh_by_chi($dayChi);

        $hourCan = $this->getCanHour($_daySolar, $_monthSolar, $_yearSolar, $_current_hour);
        $hourChi = $this->getChiHour($_current_hour);
        $hourCanChi = $hourCan . ' ' . $hourChi;
        $napAm = $this->pxg_geofesh_date_NAPAM($dayCan, $dayChi);

        $gioHoangDao = $this->getGioHoangDaoKietHung($ld->jd);
        $daiTieuNguyetInfo = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->info_default, (($this->pxg_get_day_of_month($_monthSolar, $_yearSolar) < 31) ? 'Tháng Thiếu' : 'Tháng Đủ'));

        $DATE_TULY_TUTUYET = $this->pxg_geofesh_date_TULYTUTUYET($yearChi, $dayChi, $_yearSolar, $_monthSolar, $_daySolar, $_tietKhi);
        $DATE_TUEPHA = $this->pxg_geofesh_date_TUEPHA($yearChi, $dayChi);
        $NORMAL = (object)array('solar' => (object)array('day' => $_daySolar, 'month' => $_monthSolar, 'year' => $_yearSolar, 'year_can_chi' => $yearCanChiDUONGLICH, 'month_can_chi' => $monthCanChiDUONGLICH, 'day_can_chi' => $dayCanChiDUONGLICH, 'hour_can_chi' => $hourCanChi), 'lunar' => (object)array('day' => $ld->day, 'month' => $ld->month, 'year' => $ld->year, 'year_can_chi' => $yearCanChi, 'month_can_chi' => $monthCanChi, 'day_can_chi' => $dayCanChi, 'hour_can_chi' => $hourCanChi, 'day_first_month_can_chi' => $firstDayCanChiMonth, 'hour_hoang_dao' => $gioHoangDao, 'tietkhi' => $tietkhi_info), 'hour' => $hourCanChi, 'napam' => $napAm, 'thapnhitruc' => $this->pxg_get_THAPNHITRUC($_monthSolar, $dayChi), 'kiethungnhat' => $this->pxg_get_KIETHUNGNHAT($monthChi, $dayChi), 'daitieunguyet' => $daiTieuNguyetInfo, 'nhithapbattu' => $this->pxg_geofesh_date_NHITHAPBATTU($_monthSolar, $_daySolar), 'amduongnhat' => '', 'thapnhicannhat' => $dayCan . ' (' . $this->pxg_get_ngu_hanh_by_can($dayCan) . ')', 'thapnhichinhat' => $dayChi . ' (' . $this->pxg_get_ngu_hanh_by_chi($dayChi) . ')', 'tulytutuyet' => $DATE_TULY_TUTUYET, 'tuepha' => $DATE_TUEPHA);

        //CỬU CUNG PHI TINH NĂM XEM HÔM NAY
        $VANSOTAMNGUYEN = $this->pxg_geofesh_date_TAMNGUYEN($ld->year);
        $VANSOCUUTINHYEAR = $this->pxg_geofesh_date_VANSOPHITINHYEAR($ld->year);
        //CỬU TINH THÁNG
        $VANSOCUUTINHMONTH = $this->pxg_geofesh_date_VANSOPHITINHMONTH($ld->month, $yearChi);
        //CỬU TINH NGÀY
        $VANSOCUUTINHDAY = $this->pxg_geofesh_date_VANSOPHITINHDAY($ld);
        //echo '<pre>'.var_export($VANSOCUUTINHDAY,true).'</pre><br/><br/><br/><br/>';
        //CỬU TINH GIỜ
        $VANSOCUUTINHHOUR = $this->pxg_geofesh_date_VANSOPHITINHHOUR($ld, $hourChi);
        //TAM SÁT NIÊN, NGUYỆT, NHẬT, THỜI;
        $TAMSATNIEN = $this->get_pxg_geofesh_TAMSAT($yearChi);
        $TAMSATNGUYET = $this->get_pxg_geofesh_TAMSAT($monthChi);
        $TAMSATNHAT = $this->get_pxg_geofesh_TAMSAT($dayChi);
        $TAMSATTHOI = $this->get_pxg_geofesh_TAMSAT($hourChi);
        //NGAYLUCNHAM
        $NGAYLUCNHAM = $this->pxg_geofesh_date_DAYLUCNHAM($ld->day, $ld->month);
        //GIOLUCNHAM
        $GIOLUCNHAMS = [];
        foreach (self::$CHI as $key => $_chi) {
            array_push($GIOLUCNHAMS, array('chi' => $_chi, 'lucnham' => $this->pxg_geofesh_date_HOURLUCNHAM($ld->day, $ld->month, $_chi)));
        }
        //NGAYAMDUONG: extend
        //NGAYXUATHANH: extend
        //NGAYDANGIAN: extend
        //$DAYDANGIANS=["Tam Nương","Nguyệt Kỵ","Sóc Vọng","Dương Công Kỵ","Nguyệt Tận"];
        //$DAY_NGUYETKY=[5,14,23];/*Sum=5*/
        //$DAY_SOCVONG=[15];/*Rằm*/
        //$DAY_TAMNUONG=[3,7/*Thượng Tuần*/,13,18/*Trung Tuần*/,22,27/*Hạ Tuần*/];
        //$DAY_DUONGCONGKY=["13","12","9","8","7","5","8 29","27","25","23","21","19"];/*id by month*/
        //$DAY_NGUYETTAN=[];/*Ngày cuối của các tháng âm lịch*/

        //STARBYHOUR
        $HOURS_HOANG_DAO = $this->getGioHoangDao($ld->jd);
        $STARBYHOURS = $this->pxg_geofesh_date_STARBYHOUR($HOURS_HOANG_DAO, $dayChi, $dayCan);
        //TUOIXUNGKHACNGAY
        $DAYGOOBAD = $this->pxg_geofesh_date_AGEvsDAYMONTH($dayCan, $dayChi);
        //TUOIXUNGKHACTHANG
        $MONTHGOODBAD = $this->pxg_geofesh_date_AGEvsDAYMONTH($monthCan, $monthChi);

        //Đổng Công Soạn Trạch Nhật
        $DONGCONGTRACH = $this->pxg_geofesh_date_DONGCONGTRACH($_monthSolar, $dayCanChi);
        //echo '<div style="margin-bottom:30px">Cô Thần ngày, năm: '.$COTHAN_NGAY.' '.$COTHAN_NAM.'</div>';

        $DATA = (object)array('ngaytotxau' => (object)array('day' => $DAYGOOBAD, 'month' => $MONTHGOODBAD), 'stars_hour' => $STARBYHOURS, 'hourkhongvong' => $this->pxg_geofesh_date_HOUR_KHONGVONG($dayCan, $dayChi), 'huongkiethung' => $this->pxg_geofesh_date_HUONG_KIETHUNG($dayCan, $dayChi), 'normal' => $NORMAL, 'lucnham' => (object)array('day' => $NGAYLUCNHAM, 'hour' => $GIOLUCNHAMS), 'dongcong' => $DONGCONGTRACH, 'cuucungphitinh' => (object)array('year' => $VANSOCUUTINHYEAR, 'month' => $VANSOCUUTINHMONTH, 'day' => $VANSOCUUTINHDAY, 'hour' => $VANSOCUUTINHHOUR), 'tamnguyen' => $VANSOTAMNGUYEN, 'tamsat' => array('year' => $TAMSATNIEN, 'month' => $TAMSATNGUYET, 'day' => $TAMSATNHAT, 'hour' => $TAMSATTHOI));
        $_tr .= json_encode($DATA);
        echo $_tr;
        if (isset($_POST['calendar'])) {
            exit();
        }
    }
    public function pxg_geofesh_date_TULY($_yearSolar, $_monthSolar, $_daySolar, $_tietKhi)
    {
        //Đông Chí: Từ ngày 21 tháng 12 hoặc ngày 22 tháng 12
        //Hạ Chí: Từ ngày 21 tháng 6 hoặc ngày 22 tháng 6
        //Thu Phân: Từ ngày 23 tháng 9 hoặc ngày 24 tháng 9
        //Xuân Phân: Từ ngày 20 tháng 3 hoặc ngày 21 tháng 3
        $_day_can_chi = '';
        $is_valid = false;
        if (($_daySolar == 20 || $_daySolar == 21 || $_daySolar == 22 || $_daySolar == 23 || $_daySolar == 24) && ($_monthSolar == 3 || $_monthSolar == 6 || $_monthSolar == 9 || $_monthSolar == 12)) {
            $ld = $this->getLunarDate($_daySolar, $_monthSolar, $_yearSolar);
            $_day_can_chi = $this->getDayCanChi($ld->jd);
            $_tietKhiByCurDate = $this->getTietKhi($ld->jd);
            $ldCheck = $this->getLunarDate($_daySolar + 1, $_monthSolar, $_yearSolar);
            $_tietKhiIsCheck = $this->getTietKhi($ldCheck->jd);
            switch ($_tietKhiByCurDate) {
                case 'Đại Tuyết':
                    if ($_tietKhiIsCheck === 'Đông Chí') {
                        $is_valid = true;
                    }
                    break;
                case 'Mang Chủng':
                    if ($_tietKhiIsCheck === 'Hạ Chí') {
                        $is_valid = true;
                    }
                    break;
                case 'Bạch Lộ':
                    if ($_tietKhiIsCheck === 'Thu Phân') {
                        $is_valid = true;
                    }
                    break;
                case 'Kinh Trập':
                    if ($_tietKhiIsCheck === 'Xuân Phân') {
                        $is_valid = true;
                    }
                    break;
            }
        }
        return array('tietkhi' => $_tietKhi, 'is_valid' => $is_valid, 'canchi' => $_day_can_chi);
    }
    public function pxg_geofesh_date_TUTUYET($_yearSolar, $_monthSolar, $_daySolar, $_tietKhi)
    {
        //Lập Xuân: Từ ngày 4 tháng 2 hoặc ngày 5 tháng 2
        //Lập Hạ: Từ ngày 5 tháng 5 hoặc ngày 6 tháng 5
        //Lập Thu: Từ ngày 7 tháng 8 hoặc ngày 8 tháng 8
        //Lập Đông: Từ ngày 7 tháng 11 hoặc ngày 8 tháng 11
        $_day_can_chi = '';
        $is_valid = false;
        if (($_daySolar == 4 || $_daySolar == 5 || $_daySolar == 6 || $_daySolar == 7 || $_daySolar == 8) && ($_monthSolar == 2 || $_monthSolar == 5 || $_monthSolar == 8 || $_monthSolar == 11)) {
            $ld = $this->getLunarDate($_daySolar, $_monthSolar, $_yearSolar);
            $_day_can_chi = $this->getDayCanChi($ld->jd);
            $_tietKhiByCurDate = $this->getTietKhi($ld->jd);
            $ldCheck = $this->getLunarDate($_daySolar + 1, $_monthSolar, $_yearSolar);
            $_tietKhiIsCheck = $this->getTietKhi($ldCheck->jd);
            switch ($_tietKhiByCurDate) {
                case 'Đại Hàn':
                    if ($_tietKhiIsCheck === 'Lập Xuân') {
                        $is_valid = true;
                    }
                    break;
                case 'Cốc Vũ':
                    if ($_tietKhiIsCheck === 'Lập Hạ') {
                        $is_valid = true;
                    }
                    break;
                case 'Đại Thử':
                    if ($_tietKhiIsCheck === 'Lập Thu') {
                        $is_valid = true;
                    }
                    break;
                case 'Sương Giáng':
                    if ($_tietKhiIsCheck === 'Lập Đông') {
                        $is_valid = true;
                    }
                    break;
            }
        }
        return array('tietkhi' => $_tietKhi, 'is_valid' => $is_valid, 'canchi' => $_day_can_chi);
    }
    public function pxg_geofesh_date_TULYTUTUYET($yearChi, $dayChi, $_yearSolar, $_monthSolar, $_daySolar, $_tietKhi)
    {
        //Tính $_tietKhi thuộc: Xuân Phân, Hạ Chí, Thu Phân hay Đông Chí => Can Chi 1 ngày trước đó là Tứ Ly.
        //Tính $_tietKhi thuộc: Lập Xuân, Lập Hạ, Lập Thu hay Lập Đông => Can Chi 1 ngày trước đó là Tứ Tuyệt.
        $arr = [];
        $arr['tuly'] = array('name' => 'Tứ Ly', 'date' => $this->pxg_geofesh_date_TULY($_yearSolar, $_monthSolar, $_daySolar, $_tietKhi), 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->dategoodbad, 'Tứ Ly'));
        $arr['tutuyet'] = array('name' => 'Tứ Tuyệt', 'date' => $this->pxg_geofesh_date_TUTUYET($_yearSolar, $_monthSolar, $_daySolar, $_tietKhi), 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->dategoodbad, 'Tứ Tuyệt'));
        return $arr;
    }
    public function pxg_geofesh_date_TUEPHA($yearChi, $dayChi)
    {
        $idxChi = $this->pxg_get_id_arr_str(self::$CHI, $yearChi);
        $TULYTUTUYET = ['Ngọ', 'Mùi', 'Thân', 'Dậu', 'Tuất', 'Hợi', 'Tý', 'Sửu', 'Dần', 'Mão', 'Thìn', 'Tỵ'];
        $TUEPHACHI = $TULYTUTUYET[$idxChi];
        return array('name' => 'Tuế Phá', 'chi' => ($TUEPHACHI === $dayChi) ? $TUEPHACHI : '', 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->dategoodbad, 'Tuế Phá'));
    }
    public function pxg_geofesh_date_HUONG_KIETHUNG($_dayCan, $_dayChi)
    {
        $TAITHAN_CAN = ['Giáp Ất', 'Bính Đinh', 'Mậu', 'Kỷ', 'Canh Tân', 'Nhâm', 'Quý'];
        $TAITHAN_COMPASS = ['Đông Nam', 'Đông', 'Bắc', 'Nam', 'Tây Nam', 'Tây', 'Tây Bắc'];
        $idxTAITHAN = $this->pxg_get_id_arr_str($TAITHAN_CAN, $_dayCan);
        $TAITHAN = '';
        if ($idxTAITHAN != -1) {
            $TAITHAN = $TAITHAN_COMPASS[$idxTAITHAN];
        }
        $HYTHAN_CAN = ['Giáp Kỷ', 'Ất Canh', 'Bính Tân', 'Đinh Nhâm', 'Mậu Quý'];
        $HYTHAN_COMPASS = ['Đông Bắc', 'Tây Bắc', 'Tây Nam', 'Nam', 'Đông Nam'];
        $idxHYTHAN = $this->pxg_get_id_arr_str($HYTHAN_CAN, $_dayCan);
        $HYTHAN = '';
        if ($idxHYTHAN != -1) {
            $HYTHAN = $HYTHAN_COMPASS[$idxHYTHAN];
        }
        $HACTHAN_CANCHI = [['Kỷ Dậu', 'Canh Tuất', 'Tân Hợi', 'Nhâm Tý', 'Quý Sửu', 'Giáp Dần'], ['Ất Mão', 'Bính Thìn', 'Đinh Tỵ', 'Mậu Ngọ', 'Kỷ Mùi'], ['Canh Thân', 'Tân Dậu', 'Nhâm Tuất', 'Quý Hợi', 'Giáp Tý', 'Ất Sửu'], ['Bính Dần', 'Đinh Mão', 'Mậu Thìn', 'Kỷ Tỵ', 'Canh Ngọ'], ['Tân Mùi', 'Nhâm Thân', 'Quý Dậu', 'Giáp Tuất', 'Ất Hợi', 'Bính Tý'], ['Đinh Sửu', 'Mậu Dần', 'Kỷ Mão', 'Canh Thìn', 'Tân Tỵ'], ['Nhâm Ngọ', 'Quý Mùi', 'Giáp Thân', 'Ất Dậu', 'Bính Tuất', 'Đinh Hợi'], ['Mậu Tý', 'Kỷ Sửu', 'Canh Dần', 'Tân Mão', 'Nhâm Thìn']];
        $HACTHAN_COMPASS = ['Đông Bắc', 'Đông', 'Đông Nam', 'Nam', 'Tây Nam', 'Tây', 'Tây Bắc', 'Bắc'];
        $idxHACTHAN = $this->pxg_get_id_arr2_str($HACTHAN_CANCHI, $_dayCan . ' ' . $_dayChi);
        $HACTHAN = '';
        if ($idxHACTHAN != -1) {
            $HACTHAN = $HACTHAN_COMPASS[$idxHACTHAN];
        }
        return array('taithan' => array('name' => 'Tài Thần', 'compass' => $TAITHAN), 'hythan' => array('name' => 'Hỷ Thần', 'compass' => $HYTHAN), 'hacthan' => array('name' => 'Hạc Thần', 'compass' => $HACTHAN));
    }
    public function pxg_geofesh_date_HOUR_KHONGVONG($_dayCan, $_dayChi)
    {
        $idx = $this->pxg_get_id_arr2_str(self::$KHONGVONG_LUCTHAP_HOAGIAP, $_dayCan . ' ' . $_dayChi);
        $LUCXUNG1 = ['Ngọ', 'Dậu', 'Tuất', 'Mùi', 'Thân', 'Hợi'];
        $LUCXUNG2 = ['Tý', 'Mão', 'Thìn', 'Sửu', 'Dần', 'Tỵ'];
        $_gioCo = '';
        if ($idx != -1) {
            $_gioCo = self::$KHONGVONG[$idx];
            if (!empty($_gioCo)) {
                $_arr = explode(' ', $_gioCo);
                $idx1 = $this->pxg_get_id_arr_str($LUCXUNG1, $_arr[0]);
                $idx2 = $this->pxg_get_id_arr_str($LUCXUNG1, $_arr[1]);
                if ($idx1 != -1 && $idx2 != -1) {
                    return array('co' => implode(', ', $_arr), 'hu' => $LUCXUNG2[$idx1] . ', ' . $LUCXUNG2[$idx2]);/*Giờ Cô (không vong) vs Giờ Hư*/
                } else {
                    $idx1 = $this->pxg_get_id_arr_str($LUCXUNG2, $_arr[0]);
                    $idx2 = $this->pxg_get_id_arr_str($LUCXUNG2, $_arr[1]);
                    return array('co' => implode(', ', $_arr), 'hu' => $LUCXUNG1[$idx1] . ', ' . $LUCXUNG1[$idx2]);/*Giờ Cô (không vong) vs Giờ Hư*/
                }
            }
        }
        return '';
    }
    public function pxg_geofesh_date_DONGCONGTRACH($_monthSolar, $dayCanChi)
    {
        $dongCongMonth = self::$pxg_geofesh_data->dongcong[$_monthSolar - 1];
        foreach ($dongCongMonth as $key => $_month) {
            if ($_month->code == $dayCanChi) {
                $_info = new \stdClass;
                $_info->name = $_month->code;
                $_info->summary = $_month->sid;
                $_info->description = $_month->content;
                return $_info;
            }
        }
        return '';
    }
    public function pxg_geofesh_date_STARBYHOUR($HOURs, $dayChi, $dayCan)
    {
        $STARs = [];
        foreach ($HOURs as $key => $hour) {
            $StarKietHung = $this->pxg_get_KIETHUNGTHOI($dayChi, $hour->chi);
            //TODO: sử dụng giờ để tìm các sao khác trước, nếu không có thì dùng sao Kiết Hung;
            $GIO_HY_THAN = $this->pxg_geofesh_date_GIOHYTHAN($dayCan, $hour->chi);
            $GIO_TRUONG_SINH = $this->pxg_geofesh_date_GIOTRUONGSINH($dayCan, $hour->chi);
            $GIO_THIEN_MA = $this->pxg_geofesh_date_GIOTHIENMA($dayChi, $hour->chi);
            $GIO_NHAT_KIEN = $this->pxg_geofesh_date_GIONHATKIEN($dayChi, $hour->chi);
            $GIO_LUC_HOP = $this->pxg_geofesh_date_GIOLUCHOP($dayChi, $hour->chi);
            $GIO_TAM_HOP = $this->pxg_geofesh_date_GIOTAMHOP($dayChi, $hour->chi);
            $GIO_THIENANAT_QUYNHAN = $this->pxg_geofesh_date_THIENATQUYNHAN($dayCan, $hour->chi);
            $GIO_NGU_BAT_NGO = $this->pxg_geofesh_date_NGU_BAT_NGO($dayCan, $hour->chi);
            $stars = [];
            if ($GIO_NGU_BAT_NGO == 1) {
                //array_push($stars, "Ngũ Bất Ngộ");
                array_push($stars, $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->info_default, "Ngũ Bất Ngộ"));
            }
            if ($GIO_HY_THAN == 1) {
                //array_push($stars, "Hỷ Thần");
                array_push($stars, $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->info_default, "Hỷ Thần"));
            }
            if ($GIO_TRUONG_SINH == 1) {
                //array_push($stars, "Trường Sinh");
                array_push($stars, $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->info_default, "Trường Sinh"));
            }
            if ($GIO_THIEN_MA == 1) {
                //array_push($stars, "Thiên Mã");
                array_push($stars, $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->info_default, "Thiên Mã"));
            }
            if ($GIO_NHAT_KIEN == 1) {
                //array_push($stars, "Nhật Kiến");
                array_push($stars, $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->info_default, "Nhật Kiến"));
            }
            if ($GIO_LUC_HOP == 1) {
                //array_push($stars, "Lục Hợp");
                array_push($stars, $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->info_default, "Lục Hợp"));
            }
            if ($GIO_TAM_HOP == 1) {
                //array_push($stars, "Tam Hợp");
                array_push($stars, $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->info_default, "Tam Hợp"));
            }
            if ($GIO_THIENANAT_QUYNHAN == 1) {
                //array_push($stars, "Dương Quý Nhân");
                array_push($stars, $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->info_default, "Dương Quý Nhân"));
            } else if ($GIO_THIENANAT_QUYNHAN == 2) {
                array_push($stars, $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->info_default, "Âm Quý Nhân"));
            }
            if (count($stars) == 0) {/*Nếu không có sao nào lấy sao Kiết Hung*/
                array_push($stars, $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->hoangdaohacdao, $StarKietHung));
            }
            array_push($STARs, (object)array('hour' => $hour->hour, 'chi' => $hour->chi, 'star' => $stars));
        }
        return $STARs;
    }
    public function pxg_geofesh_date_THIENATQUYNHAN($dayCan, $hourChi)
    {
        $idxCAN = $this->pxg_get_id_arr_str(self::$CAN, $dayCan);
        $HOURS_THIENAT_DUONG_QUYNHAN = ["Mùi", "Thân", "Dậu", "Hợi", "Sửu", "Tý", "Sửu", "Dần", "Mão", "Tỵ"];
        $HOURS_THIENAT_AM_QUYNHAN = ["Sửu", "Tý", "Hợi", "Dậu", "Mùi", "Thân", "Mùi", "Ngọ", "Tỵ", "Mão"];
        if ($HOURS_THIENAT_DUONG_QUYNHAN[$idxCAN] == $hourChi) {
            return 1; //Giờ Thiên Ất Quý Nhân: Dương Quý Nhân;
        } else if ($HOURS_THIENAT_AM_QUYNHAN[$idxCAN] == $hourChi) {
            return 2; //Giờ Thiên Ất Quý Nhân: Âm Quý Nhân;
        }
        return 0;
    }
    public function pxg_geofesh_date_NGU_BAT_NGO($dayCan, $hourChi)
    {
        $idxCAN = $this->pxg_get_id_arr_str(self::$CAN, $dayCan);
        //$HOURS_NGU_BAT_NGO_CAN=["Canh","Tân","Nhâm","Quý","Giáp","Ất","Bính","Đinh","Mậu","Kỷ"];
        $HOURS_NGU_BAT_NGO_CHI = ["Ngọ", "Tỵ", "Thìn", "Mão", "Dần", "Sửu Hợi", "Tý Tuất", "Dậu", "Thân", "Mùi"];
        if (stripos($HOURS_NGU_BAT_NGO_CHI[$idxCAN], $hourChi) !== false) {
            return 1; //Ngũ Bất Ngộ;
        }
        return 0;
    }
    public function pxg_geofesh_date_GIOHYTHAN($dayCan, $hourChi)
    {
        $idxCAN = $this->pxg_get_id_arr_str(self::$CAN, $dayCan);
        $HOURS_HY_THAN = ["Hợi", "Ngọ", "Dần", "Dậu", "Dần", "Dậu", "Tỵ", "Tý", "Thân", "Mão"];
        if ($HOURS_HY_THAN[$idxCAN] == $hourChi) {
            return 1; //Giờ Hỷ Thần;
        }
        return 0;
    }
    public function pxg_geofesh_date_GIOTRUONGSINH($dayCan, $hourChi)
    {
        $idxCAN = $this->pxg_get_id_arr_str(self::$CAN, $dayCan);
        $HOURS_TRUONG_SINH = ["Dần", "Tuất", "Thân", "Ngọ", "Thìn", "Dần", "Tuất", "Thân", "Ngọ", "Thìn"];
        if ($HOURS_TRUONG_SINH[$idxCAN] == $hourChi) {
            return 1; //Giờ Trường Sinh;
        }
        return 0;
    }
    public function pxg_geofesh_date_GIOTHIENMA($dayChi, $hourChi)
    {
        $idxCHI = $this->pxg_get_id_arr_str(self::$CHI, $dayChi);
        $HOURS_THIEN_MA = ["Dần", "Hợi", "", "Tỵ", "Dần", "", "Thân", "Tỵ", "", "Hợi", "Thân", ""];
        if ($HOURS_THIEN_MA[$idxCHI] == $hourChi) {
            return 1; //Giờ Thiên Mã;
        }
        return 0;
    }
    public function pxg_geofesh_date_GIONHATKIEN($dayChi, $hourChi)
    {
        $idxCHI = $this->pxg_get_id_arr_str(self::$CHI, $dayChi);
        $HOURS_NHAT_KIEN = ["Tý", "Sửu", "Dần", "Mão", "", "Tỵ", "", "Mùi", "Thân", "", "Tuất", ""];
        if ($HOURS_NHAT_KIEN[$idxCHI] == $hourChi) {
            return 1; //Giờ Nhật Kiến;
        }
        return 0;
    }
    public function pxg_geofesh_date_GIOLUCHOP($dayChi, $hourChi)
    {
        $idxCHI = $this->pxg_get_id_arr_str(self::$CHI, $dayChi);
        $HOURS_LUC_HOP = ["Sửu", "Tý", "Hợi", "Tuất", "Dậu", "Thân", "Mùi", "Ngọ", "Tỵ", "Thìn", "Mão", "Dần"];
        if ($HOURS_LUC_HOP[$idxCHI] == $hourChi) {
            return 1; //Giờ Lục Hợp;
        }
        return 0;
    }
    public function pxg_geofesh_date_GIOTAMHOP($dayChi, $hourChi)
    {
        $idxCHI = $this->pxg_get_id_arr_str(self::$CHI, $dayChi);
        $HOURS_TAM_HOP = ["Thân Thìn", "Tỵ Dậu", "Ngọ Tuất", "Hợi Mùi", "Thân Tý", "Dậu Sửu", "Dần Tuất", "Hợi Mão", "Tý Thìn", "Tỵ Sửu", "Dần Ngọ", "Mão Mùi"];
        if (stripos($HOURS_TAM_HOP[$idxCHI], $hourChi) !== false) {
            return 1; //Giờ Tam Hợp;
        }
        return 0;
    }
    public function pxg_geofesh_date_AGEvsDAYMONTH($_can, $_chi)
    {
        $idxLUCTHAP_HOAGIAP = $this->pxg_get_id_arr_str(self::$DAYCANCHI_LUCTHAP_HOAGIAP, $_can . ' ' . $_chi);
        return self::$AGEGOOBAD[$idxLUCTHAP_HOAGIAP];
    }
    public function pxg_get_XUNGKHAC_HOP_XUNG_HINH_PHA_HAI($yearCan, $yearChi, $dayCan, $dayChi, $yearNguHanh, $dayNguHanh)
    {
        return '';
        //Ngày 1: Can Chi năm đầu vào trùng với các ngày có CanChi đó là => Ngày Xấu
        //Ngày 2: can ngày trùng can năm và chi ngày xung chi năm.
        //Ngày 3: chi ngày trùng chi năm và can ngày Xung Khắc can năm
        //Ngày 4: can chi ngày xung khắc can chi năm + Ngũ Hành năm khắc Ngũ Hành ngày.
        //Ngày 5: ngũ hành năm khắc ngày

        /*$CAN_YEAR_DAY_XUNGKHAC=$this->pxg_geofesh_date_check_THIENCAN_XUNGKHAC($yearCan,$dayCan);
    echo '<div>Năm: '.$yearCan.' <strong>'.$CAN_YEAR_DAY_XUNGKHAC.'</strong> Ngày: '.$dayCan.'</div>';
    //Ngày 1: CanChi năm trùng CanChi ngày sinh => Ngày Xấu
    if($CAN_YEAR_DAY_XUNGKHAC==1){
        array_push($XUNGKHACNGAY, $dayCanChi);
    }
    
    $CHI_YEAR_DAY_XUNGKHAC=$this->pxg_geofesh_date_check_DIACHI_XUNGKHAC($yearChi,$dayChi);
    if($yearCan==$dayCan && $CHI_YEAR_DAY_XUNGKHAC==1){
        array_push($XUNGKHACNGAY, $dayCanChi);
    }
    //Ngày 3: chi ngày trùng chi năm và can ngày Xung Khắc can năm
    echo '<div>Khắc Ngày: '.implode(', ',$XUNGKHACNGAY).'</div>';
    if($yearChi==$dayChi && $CAN_YEAR_DAY_XUNGKHAC==1){
        //Khắc
    }
    //Ngày 4: can chi ngày xung khắc can chi năm + Ngũ Hành năm khắc Ngũ Hành ngày.
    $NGUHANH_YEAR_DAY_XUNGKHAC=$this->pxg_geofesh_date_check_NGUHANH_XUNGKHAC($yearNguHanh,$dayNguHanh);
    if($CAN_YEAR_DAY_XUNGKHAC==1 && $CHI_YEAR_DAY_XUNGKHAC==1 && $NGUHANH_YEAR_DAY_XUNGKHAC==1){
        //Khắc đại kỵ;
    }
    //Ngày 5: ngũ hành năm khắc ngày
    if($NGUHANH_YEAR_DAY_XUNGKHAC==1){
        //Khắc ngũ hành;
    }*/
    }
    public function pxg_geofesh_date_check_NGUHANH_XUNGKHAC($_nguHanh1, $_nguHanh2)
    {
        //echo 'Check xung khắc ngũ hành năm: '.$_nguHanh1.' vs ngày '.$_nguHanh2.'<br/>';
        $_nguHanh1 = str_replace(array('+', '-'), '', $_nguHanh1);
        $_nguHanh2 = str_replace(array('+', '-'), '', $_nguHanh2);
        $NGUHANH_XUNGKHAC1 = ["Thủy", "Hỏa", "Kim", "Mộc", "Thổ"];
        $NGUHANH_XUNGKHAC2 = ["Hỏa", "Kim", "Mộc", "Thổ", "Thủy"];
        $idx1 = $this->pxg_get_id_arr_str($NGUHANH_XUNGKHAC1, $_nguHanh1);
        $idx2 = $this->pxg_get_id_arr_str($NGUHANH_XUNGKHAC2, $_nguHanh2);
        //echo $_nguHanh1.'('.$idx1.') vs '.$_nguHanh2.'('.$idx2.') <br/>';
        if ($idx1 == $idx2 && $idx1 != -1 && $idx2 != -1) {
            //echo $_nguHanh1.' KHẮC '.$_nguHanh2.'<br/>';
            return 1;
        }
        return 0;
    }
    public function pxg_geofesh_date_check_DIACHI_XUNGKHAC($_chi1, $_chi2)
    {
        /*Chi Xung Khắc dựa theo vị trí 12 địa chi self::$CHI =["Tý","Sửu","Dần","Mão","Thìn","Tỵ","Ngọ","Mùi","Thân","Dậu","Tuất","Hợi"];*/
        $CHI_XUNGKHAC_YEAR_DAY = [["", "Tam Hội,Lục Hợp", "", "Hình Vô Lễ", "Tam Hợp", "", "Xung", "Lục Hại", "Tam Hợp", "Lục Phá", "Tam Hội"]/*Tý-Tý*/, ["Tam Hội,Lục Hợp", "", "", "", "Lục Phá", "Tam Hợp", "Lục Hại", "Xung,Hình Vô Ơn", "", "Tam Hợp", "Hình Vô Ơn", "Tam Hội"]/*Sửu*/, ["", "", "", "Tam Hội", "Tam Hội", "Hình Trì Thế,Lục Hại", "Tam Hợp", "", "Xung,Hình Trì Thế", "", "Tam Hợp", "Lục Hợp"]/*Dần*/, ["Hình Vô Lễ", "", "Tam Hội", "", "Tam Hội,Lục Hợp", "", "Lục Phá", "Tam Hợp", "", "Xung", "Lục Hợp", "Tam Hợp"]/*Mão*/, ["Tam Hợp", "Lục Phá", "Tam Hội", "Tam Hội,Lục Hại", "Hình", "", "", "", "Tam Hợp", "Lục Hợp", "Xung", ""]/*Thìn*/, ["", "Tam Hợp", "Hình Trì Thế,Lục Hại", "", "", "", "Tam Hội", "Tam Hội", "Hình Trì Thế,Lục Hợp,Lục Phá", "Tam Hợp", "", "Xung"]/*Tỵ*/, ["Xung", "Lục Hại", "Tam Hợp", "Lục Phá", "", "Tam Hội", "Hình", "Tam Hội,Lục Hợp", "", "", "Tam Hợp", ""]/*Ngọ*/, ["Lục Hại", "Xung,Hình Vô Ơn", "", "Tam Hợp", "", "Tam Hội", "Tam Hội,Lục Hợp", "", "", "", "Hình Vô Ơn,Lục Phá", "Tam Hợp"]/*Mùi*/, ["Tam Hợp", "", "Xung,Hình Trì Thế", "", "Tam Hợp", "Hình Trì Thế,Lục Hợp,Lục Phá", "", "", "", "Tam Hội", "Tam Hội", "Lục Hại"]/*Thân*/, ["Lục Phá", "Tam Hợp", "", "Xung", "Lục Hợp", "Tam Hợp", "", "", "Tam Hội", "Hình", "Tam Hội,Lục Hại", ""]/*Dậu*/, ["", "Hình Vô Ơn", "Tam Hợp", "Lục Hợp", "Xung", "", "Tam Hợp", "Hình Vô Ơn,Lục Phá", "Tam Hội", "Tam Hội,Lục Hại", "", ""]/*Tuất*/, ["Tam Hội", "Tam Hội", "Lục Hợp", "Lục Hợp", "", "Xung", "", "Tam Hợp", "Lục Hại", "", "", "Hình"]];
        $idxCHI_YEAR = $this->pxg_get_id_arr_str(self::$CHI, $_chi1);
        $idxCHI_DAY = $this->pxg_get_id_arr_str(self::$CHI, $_chi2);
        $CHI_XUNG_KHAC_YEAR = $CHI_XUNGKHAC_YEAR_DAY[$idxCHI_YEAR];
        $CHI_XUNGKHAC = $CHI_XUNG_KHAC_YEAR[$idxCHI_DAY];
        if (stripos($CHI_XUNGKHAC, 'Xung') !== false) {/*Xung Khắc*/
            return 1;
        }
        //echo 'Chi ngày: '.self::$CHI[$idxCHI_DAY]. ' '.$CHI_XUNGKHAC.' là kết quả XUNG KHẮC với Chi năm '.$_chi1.': '.implode('|', $CHI_XUNG_KHAC_YEAR).'<br/>';
        return 0;
    }
    public function pxg_geofesh_date_check_THIENCAN_XUNGKHAC($_can1, $_can2)
    {
        //self::$CAN = ["Giáp","Ất","Bính","Đinh","Mậu","Kỷ","Canh","Tân","Nhâm","Quý"];
        $CAN_XUNG_KHAC = ["Mậu", "Kỷ", "Canh", "Tân", "Nhâm", "Quý", "Giáp", "Ất", "Bính", "Đinh"];
        $idxCan1 = $this->pxg_get_id_arr_str(self::$CAN, $_can1);
        $idxCan2 = $this->pxg_get_id_arr_str($CAN_XUNG_KHAC, $_can2);
        //echo '<div>'.$idxCan1.' vs '.$idxCan2.'</div>';
        if ($idxCan1 == $idxCan2 && $idxCan1 != -1 && $idxCan2 != -1) {
            //echo '<div>'.self::$CAN[$idxCan1].' KHẮC '.$CAN_XUNG_KHAC[$idxCan2].'</div>';
            return 1; //Xung Khắc;
        }
        return 0; //Không Xung Khắc;
    }
    public function pxg_geofesh_date_HOURLUCNHAM($_dayLunar, $_monthLunar, $_hourChi)
    {
        $HOURLUCNHAMS = ["Không Vong", "Đại An", "Tốc Hỷ", "Lưu Liên", "Xích Khẩu", "Tiểu Cát"];
        $KHAC = [1, 2, 3, 4, 5, 6, 1, 2, 3, 4, 5, 6];/*Ứng với 12 CHI*/
        $khacKhoiHanh = $this->pxg_get_id_arr(self::$CHI, $_hourChi);
        $X = (($_dayLunar + $_monthLunar + $khacKhoiHanh) - 2) % 6;
        $data = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->lucnham, $HOURLUCNHAMS[$X]);
        return $data;
    }
    public function pxg_geofesh_date_DAYLUCNHAM($_dayLunar, $_monthLunar)
    {
        $LUCNHAPTHANG = [[1, 7], [2, 8], [3, 9], [4, 10], [5, 11], [6, 12]];
        $NGAYLUCNHAMS = ["Đại An", "Lưu Liên", "Tốc Hỷ", "Xích Khẩu", "Tiểu Cát", "Không Vong"];
        $idx = $this->pxg_get_id_arr2($LUCNHAPTHANG, $_monthLunar); //Cũng là ngày lục nhâm đầu tháng;
        $idxNGAYLUCNHAMDAUTHANG = $NGAYLUCNHAMS[$idx];
        $X1 = ($idx + $_dayLunar - 1) % 6;
        while ($X1 > 6) {
            $X1 = $X1 % 6;
        }
        $data = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->lucnham, $NGAYLUCNHAMS[$X1]);
        return (object)array('lucnham' => $data, 'day' => $X1, 'lucnham_first_month' => $idxNGAYLUCNHAMDAUTHANG);
        //đưa tháng âm vào $LUCNHAPTHANG tìm ra vị trí mảng của tháng. Lấy vị trí đó đưa vào bảng sau để lấy ra ngày lục nhâm đầu tiên của tháng $NGAYLUCNHAM;
        //Có vị trí ngày đầu tiên của tháng rồi tính các ngày tiếp theo theo chiều thuận.
        //25/8 =(2(khởi)-1=1)+(25mod6=1)=2;(vị trí trong $NGAYLUCNHAM[2-1]) 1. Lưu Liên
        //11/8 =(2(khởi)-1=1)+(11mod6=5)=6;(vị trí trong $NGAYLUCNHAM[6-1]) 5. Không Vong
        //22/6 = (6(khởi)-1=5)+(22mod6=4)=9%6=3;(vị trí trong $NGAYLUCNHAM[3-1]); 2. Tốc Hỷ
        //4/6 = (6(khởi)-1=5)+(4mod6=4)=9, 9 lớn 6 = > 9%6=3;(vị trí trong $NGAYLUCNHAM[3-1]); 2. Tốc Hỷ
        //27/5 = (5(khởi)-1=4)+(27mod6=3)=7 lớn 6 => 7%6 = 1;(vị trí trong $NGAYLUCNHAM[1-1]); 0. Đại An
    }
    public function pxg_geofesh_date_VANSOPHITINHHOUR($lunarYearInfo, $_hourChi)
    {
        //Xác định CHI ngày, Tiết Khí, Chi Giờ => Giờ Cửu Tinh
        //$_hourChi='Tuất';
        $_tietKhi = $this->getTietKhi($lunarYearInfo->jd);
        $_dayChi = $this->getDayChi($lunarYearInfo->jd);
        /*echo 'Tiết khí '.$_tietKhi.'<br/>';
    echo 'Chi ngày '.$_dayChi.'<br/>';
    echo 'Chi giờ '.$_hourChi.'<br/>';*/
        $DONGCHI_HACHI = $this->pxg_geofesh_date_IS_TIETKHI_DONGCHI_HACHI($_tietKhi);
        $idxDayChi = $this->pxg_get_id_arr_str(self::$CUUTINH_HOUR_CHI_DAY, $_dayChi);
        $GIOCUUTINH_CHI_BY_CHI_DAY = self::$GIOCUUTINH_CHI[$idxDayChi];
        $idxHourInChi = $this->pxg_get_id_arr(self::$CHI, $_hourChi);
        if ($DONGCHI_HACHI == 0) { //Sau Đông Chí
            $VANSOPHITINH = $GIOCUUTINH_CHI_BY_CHI_DAY[0][$idxHourInChi];
        } else { //Sau Hạ Chí
            $VANSOPHITINH = $GIOCUUTINH_CHI_BY_CHI_DAY[1][$idxHourInChi];
        }
        $CUUCUNGCOMPASS = $this->pxg_geofesh_date_LUONGTHIENXICH_TRUNGCUNG($VANSOPHITINH);
        $data = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->cuucungphitinh, self::$CUUCUNGPHITINH[$VANSOPHITINH - 1]);
        return (object)array('type' => 'Giờ', 'vanso' => $VANSOPHITINH, 'cuutinh' => $data, 'compass' => $CUUCUNGCOMPASS);
    }
    public function pxg_geofesh_date_IS_TIETKHI_DONGCHI_HACHI($_tietKhi)
    {
        //Xác định Tiết Khí hiện tại SAU ĐÔNG CHÍ hay SAU HẠ CHÍ
        return $this->pxg_get_id_arr2_str(self::$TIETKHI_CUUTINH_HOUR, $_tietKhi);
    }
    public function pxg_geofesh_date_VANSOPHITINHDAY($lunarYearInfo)
    {
        $_tietKhi = $this->getTietKhi($lunarYearInfo->jd);
        $_dayCanChi = $this->getDayCanChi($lunarYearInfo->jd);
        $idxTIETKHICUUTINH = $this->pxg_get_id_arr2_str(self::$TIETKHI_CUUTINH, $_tietKhi);
        $VANSOPHITINH = self::$CUUTINH_NGAY_THEO_TIETKHI[$idxTIETKHICUUTINH];
        $KHOI_THUAN_NGHICH = '';
        if ($VANSOPHITINH == 1 || $VANSOPHITINH == 7 || $VANSOPHITINH == 4) {/*Khởi thuận*/
            $KHOI_THUAN_NGHICH = 'thuan';
        } else {/*9,3,6 khởi nghịch*/
            $KHOI_THUAN_NGHICH = 'nghich';
        }
        $CUUTINH_KHOI = self::$CUUCUNGPHITINH[$VANSOPHITINH - 1];
        $idxLUCTHAP_HOAGIAP = $this->pxg_get_id_arr_str(self::$DAYCANCHI_LUCTHAP_HOAGIAP, $_dayCanChi);
        $DAY_LUCTHAP_HOAGIAP = $idxLUCTHAP_HOAGIAP;
        $idx_cuutinh_day = $this->pxg_get_id_arr(self::$CUUTINH_NGAY_THEO_TIETKHI, $VANSOPHITINH);
        $VANTRUNGCUNG = self::$CUUTINH_DAY_THEO_TIETKHI[$idx_cuutinh_day][$DAY_LUCTHAP_HOAGIAP];
        $CUUCUNGCOMPASS = $this->pxg_geofesh_date_LUONGTHIENXICH_TRUNGCUNG($VANTRUNGCUNG - 1, 1);
        $data = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->cuucungphitinh, self::$CUUCUNGPHITINH[$VANTRUNGCUNG - 1]);
        return (object)array('type' => 'Ngày', 'vanso' => $VANTRUNGCUNG, 'khoi' => $KHOI_THUAN_NGHICH, 'cuutinh' => $data, 'compass' => $CUUCUNGCOMPASS);
    }
    public function pxg_geofesh_date_get_CUUTINHPHICUNG($_thuan_nghich, $idx_khoicung)
    {
        $CUUCUNGPHITINH_BY_TIETKHI = [];
        if ($_thuan_nghich == 'nghich') {
            for ($i = $idx_khoicung; $i >= 0; $i--) {
                array_push($CUUCUNGPHITINH_BY_TIETKHI, self::$CUUCUNGPHITINH[$i]);
            }
            if ($idx_khoicung < 9) {
                for ($i = count(self::$CUUCUNGPHITINH) - 1; $i > $idx_khoicung; $i--) {
                    array_push($CUUCUNGPHITINH_BY_TIETKHI, self::$CUUCUNGPHITINH[$i]);
                }
            }
        } else {
            for ($i = $idx_khoicung; $i < count(self::$CUUCUNGPHITINH); $i++) {
                array_push($CUUCUNGPHITINH_BY_TIETKHI, self::$CUUCUNGPHITINH[$i]);
            }
            if ($idx_khoicung != 0) {
                for ($i = 0; $i < $idx_khoicung; $i++) {
                    array_push($CUUCUNGPHITINH_BY_TIETKHI, self::$CUUCUNGPHITINH[$i]);
                }
            }
        }
        return $CUUCUNGPHITINH_BY_TIETKHI;
    }
    public function get_pxg_geofesh_TAMSAT($_chi)
    {
        $idxTamSatChi = -1;
        for ($i = 0; $i < count(self::$TAMSATCHI); $i++) {
            $idx = $this->pxg_get_id_arr_str(self::$TAMSATCHI[$i], $_chi);
            if ($idx != -1) {
                $idxTamSatChi = $i;
                break;
            }
        }
        return self::$TAMSAT[$idxTamSatChi];
    }
    public function pxg_geofesh_date_LUONGTHIENXICH_TRUNGCUNG($vanso, $compass_plus = 0)
    {
        $LUONGTHIENXICH_TRUNGCUNG = [];
        $compass_len = (($compass_plus == 1) ? 8 : 9);
        for ($huong = $vanso; $huong < $compass_len; $huong++) {
            $LUONGTHIENXICH_TRUNGCUNG[] = $huong + 1 + $compass_plus;
        }
        if ($vanso > 0) {
            for ($huong = 0; $huong < $vanso; $huong++) {
                $LUONGTHIENXICH_TRUNGCUNG[] = $huong + 1;
            }
        }
        $CUUCUNGCOMPASS = [];
        for ($cungPhiCompass = 0; $cungPhiCompass < count(self::$LUONGTHIENXICH); $cungPhiCompass++) {
            $CUUCUNGCOMPASS[self::$LUONGTHIENXICH[$cungPhiCompass]] = (object)array("name" => self::$LUONGTHIENXICH[$cungPhiCompass], "vanso" => $LUONGTHIENXICH_TRUNGCUNG[$cungPhiCompass], 'cuutinh' => self::$CUUCUNGPHITINH[$LUONGTHIENXICH_TRUNGCUNG[$cungPhiCompass] - 1]);
        }
        return $CUUCUNGCOMPASS;
    }
    public function pxg_geofesh_date_VANSOPHITINHMONTH($_month, $_yearChi)
    {
        $CTINH = self::$CUUTINHMONTH[$_month - 1];
        $VANSOPHITINH = $CTINH[$this->pxg_get_id_arr_str(self::$CUUTINHMONTHYEAR, $_yearChi)];
        $CUUCUNGCOMPASS = $this->pxg_geofesh_date_LUONGTHIENXICH_TRUNGCUNG($VANSOPHITINH);
        $data = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->cuucungphitinh, self::$CUUCUNGPHITINH[$VANSOPHITINH - 1]);
        return (object)array('type' => 'Tháng', 'vanso' => $VANSOPHITINH, 'cuutinh' => $data, 'compass' => $CUUCUNGCOMPASS);
    }
    public function pxg_geofesh_date_VANSOPHITINHYEAR($_year)
    {
        $X1 = abs($_year - 1864);/*Chu kỳ TAM NGUYÊN*/
        //"Nhất Bạch". Khởi vận 1 năm Giáp Tý 1864 là Nhất Bạch, năm >1864 NGHỊCH 1865 là Cửu Tử đi ngược vòng và năm <1864 THUẬN tới là Nhị Hắc.
        if ($X1 > 180) {
            $X1 = $X1 % 180;
        }
        //TODO: Cửu Cung Phi Tinh: Chiều Lương Thiên Xích: Trung Cung => TÂY BẮC,TÂY,ĐÔNG BẮC,NAM,BẮC,TÂY NAM,ĐÔNG,ĐÔNG NAM
        //1. XÁC ĐỊNH Phi Tinh làm TRUNG CUNG.
        $VANSOPHITINH = ($_year > 1864) ?/*nghịch*/ abs(9 - ($X1 % 9)) :/*thuận*/ abs($X1 % 9);/*1-9*/
        //$VANSOPHITINH=$VANSOPHITINH+1;
        $CUUCUNGCOMPASS = $this->pxg_geofesh_date_LUONGTHIENXICH_TRUNGCUNG($VANSOPHITINH, 1);
        $data = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->cuucungphitinh, self::$CUUCUNGPHITINH[$VANSOPHITINH % 9]);
        return (object)array('type' => 'Năm', 'vanso' => ($VANSOPHITINH % 9 + 1), 'cuutinh' => $data, 'compass' => $CUUCUNGCOMPASS);
    }
    public function pxg_geofesh_date_TAMNGUYEN($_year)
    {
        $_year = isset($_POST['year']) ? $_POST['year'] : (isset($args['year']) ? $args['year'] : date('Y'));
        if (!empty($_year)) {
            $_year = intval($_year);
        }
        $X1 = abs($_year - 1864);/*Chu kỳ TAM NGUYÊN*/
        //"Nhất Bạch". Khởi vận 1 năm Giáp Tý 1864 là Nhất Bạch, năm >1864 NGHỊCH 1865 là Cửu Tử đi ngược vòng và năm <1864 THUẬN tới là Nhị Hắc.
        if ($X1 > 180) {
            $X1 = $X1 % 180;
        }
        $VANSOPHITINH = ($_year > 1864) ?/*nghịch*/ abs(9 - ($X1 % 9)) :/*thuận*/ abs($X1 % 9);/*1-9*/
        //CÁCH TÍNH VẬN SỐ CHỦ ĐẠO Cửu CUNG cho chu kỳ TAM NGUYÊN;
        $VANSO = -1;
        $VANCHU = '';
        $VANSOPHITINH = -1;
        if ($X1 <= 60) {
            if ($X1 < 20) {
                $VANSO = 1; //Vận chủ THƯỢNG NGUYÊN //"Nhất Bạch". Khởi vận 1 năm Giáp Tý 1864 là Nhất Bạch
            } else if ($X1 >= 20 && $X1 < 40) {
                $VANSO = 2; //"Nhị Hắc"
            } else {
                $VANSO = 3; //"Tam Bích"
            }
            $CUUTINH = 'Nhất Bạch';
            $CHUKY = 'Thượng Nguyên';
            $VANCHU = 1;
        } else if ($X1 > 60 && $X1 <= 120) {
            if ($X1 < 80) {
                $VANSO = 4; //Vận chủ TRUNG NGUYÊN //"Tứ Lục"
            } else if ($X1 >= 80 && $X1 < 100) {
                $VANSO = 5; //"Ngũ Hoàng Đại Sát"
            } else {
                $VANSO = 6; //"Lục Bạch"
            }
            $CUUTINH = 'Tứ Lục';
            $CHUKY = 'Trung Nguyên';
            $VANCHU = 4;
        } else {
            if ($X1 <= 140) {
                $VANSO = 7; //Vận chủ HẠ NGUYÊN //"Thất Xích"
            } else if ($X1 > 140 && $X1 < 160) {
                $VANSO = 8; //"Bát Bạch"
            } else {
                $VANSO = 9; //"Cửu Tử"
            }
            $CUUTINH = 'Thất Xích';
            $CHUKY = 'Hạ Nguyên';
            $VANCHU = 7;
        }
        return (object)array('year' => $_year, 'vanchu' => $VANCHU, 'van' => $VANSO, 'chuky' => $CHUKY, 'cuutinh' => $CUUTINH);
    }
    public function pxg_geofesh_date_NAPAM($can, $chi)
    {
        $can_chi = $can . ' ' . $chi;
        $iCanIDX = 0;
        for ($iCan = 0; $iCan < count(self::$CAN_NAPAM); $iCan++) {
            if (stripos(self::$CAN_NAPAM[$iCan], $can) !== false) {
                $iCanIDX = $iCan + 1;
                break;
            }
        }
        $iChiIDX = 0;
        for ($iChi = 0; $iChi < count(self::$CHI_NAPAM); $iChi++) {
            if (stripos(self::$CHI_NAPAM[$iChi], $chi) !== false) {
                $iChiIDX = $iChi;
                break;
            }
        }
        $iNguHanh = $iCanIDX + $iChiIDX;
        while ($iNguHanh > 5) {
            $iNguHanh -= 5;
        }
        $iTIEUDAITHANHIDX = -1;
        for ($i = 0; $i < count(self::$TIEUTHANHCANCHI); $i++) {
            for ($j = 0; $j < count(self::$TIEUTHANHCANCHI[$i]); $j++) {
                if (self::$TIEUTHANHCANCHI[$i][$j] == $can_chi) {
                    $iTIEUDAITHANHIDX = $i;
                    break;
                }
            }
        }
        if ($iTIEUDAITHANHIDX == -1) {
            for ($i = 0; $i < count(self::$DAITHANHCANCHI); $i++) {
                for ($j = 0; $j < count(self::$DAITHANHCANCHI[$i]); $j++) {
                    if (self::$DAITHANHCANCHI[$i][$j] == $can_chi) {
                        $iTIEUDAITHANHIDX = $i;
                        break;
                    }
                }
            }
            $DAITIEUTHANH = self::$DAITHANH[$iTIEUDAITHANHIDX];
        } else {
            $DAITIEUTHANH = self::$TIEUTHANH[$iTIEUDAITHANHIDX];
        }
        $_name = $DAITIEUTHANH . ' ' . self::$NGUHANH_NAPAM[$iNguHanh - 1];
        return array('name' => $_name, 'info' => $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->napam, $_name));
    }
    public function pxg_geofesh_date_NHITHAPBATTU($month, $day)
    {
        $_firstDayNameByMonth = date('D', mktime(0, 0, 0, $month, 1));
        $DAYNO = self::pxg_get_id_arr(self::$DAYNAME, $_firstDayNameByMonth);
        $SAO1 = self::$NHITHAPBATTU[$DAYNO][0];
        $NHITHAPBATTUDAYARRANGE = [];
        $idx_sao1 = self::pxg_get_id_arr(self::$NHITHAPBATTUDAY, $SAO1);
        if ($idx_sao1 == 0) {
            $NHITHAPBATTUDAYARRANGE = self::$NHITHAPBATTUDAY;
        } else {
            for ($i = $idx_sao1; $i < count(self::$NHITHAPBATTUDAY); $i++) {
                array_push($NHITHAPBATTUDAYARRANGE, self::$NHITHAPBATTUDAY[$i]);
            }
            for ($i = 0; $i < $idx_sao1; $i++) {
                array_push($NHITHAPBATTUDAYARRANGE, self::$NHITHAPBATTUDAY[$i]);
            }
        }
        $BATTU = $NHITHAPBATTUDAYARRANGE[(($day > 28) ? $day % 28 : $day) - 1];
        $_DayNameByMonth = date('D', mktime(0, 0, 0, $month, $day));
        $DAYNOTODAY = self::pxg_get_id_arr(self::$DAYNAME, $_DayNameByMonth);
        $NHITHAPBATTUORIGINAL = self::$NHITHAPBATTU[$DAYNOTODAY];
        $IDX = self::pxg_get_id_arr($NHITHAPBATTUORIGINAL, $BATTU);
        $LINHVAT = self::$NHITHAPBATTULINHVAT[$DAYNOTODAY][$IDX];
        $LINHVATHANNGU = self::$NHITHAPBATTULINHVATHANNGU[$DAYNOTODAY][$IDX];
        $NGUHANH = self::$NHITHAPBATTUNGUHANH[$DAYNOTODAY][$IDX];
        $TOTXAU = self::$NHITHAPBATTUTOTXAU[$DAYNOTODAY][$IDX];
        $_name = $BATTU . ' ' . $NGUHANH . ' ' . $LINHVATHANNGU;
        $data = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->nhithapbattu, $_name);
        return $data;
    }
    public function pxg_get_THAPNHITRUC($month, $dayChi)
    {
        $arrThapNhiTruc = self::$THAPNHITRUCTHANG[$month - 1];
        $_name = 'Trực ' . self::$THAPNHITRUC[self::pxg_get_id_arr($arrThapNhiTruc, $dayChi)];
        $data = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->thapnhitruc, $_name);
        return $data;
    }

    public function getHoangDaoHacDao($day_hoang_dao, $name) {
        $result = str_replace($name, '', $day_hoang_dao);
        return trim(preg_replace('/\s+/', ' ', $result));
    }

    public function pxg_get_KIETHUNGNHAT($monthChi, $dayChi)
    {
        $arrKietHung = self::$KIETHUNGNGAY[self::pxg_get_id_arr(self::$CHI, $monthChi)];
        $kiet_hung = self::$KIETHUNGNHAT[self::pxg_get_id_arr($arrKietHung, $dayChi)];
        $_info = null;// $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->hoangdaohacdao, $kiet_hung);
        $day_hoang_dao = self::$KIETHUNGNHATTOTXAU[self::pxg_get_id_arr($arrKietHung, $dayChi)];
        $hoang_dao_hac_dao = self::getHoangDaoHacDao($day_hoang_dao , $kiet_hung);
        $is_hoang_dao = $hoang_dao_hac_dao === 'Hoàng Đạo' ?? false;
        return (object)array('kiet_hung' => $kiet_hung,
        'is_good_zodiac' => $is_hoang_dao,
        'is_bad_zodiac' => !$is_hoang_dao,
        'zodiac' => $hoang_dao_hac_dao,
        'display' => $day_hoang_dao, 'info' => $_info);
    }

    public function pxg_get_KIETHUNGTHOI($dayChi, $hourChi)
    {
        $arrKietHungHour = self::$KIETHUNGNGAY[self::pxg_get_id_arr(self::$CHI, $dayChi)];
        return self::$KIETHUNGNHAT[self::pxg_get_id_arr($arrKietHungHour, $hourChi)];
    }
    /*Ngày tốt xấu*/

    /*GET Infor*/
    public function pxg_geofesh_get_info_DATA_GEO($_data_geo, $_name, $_class_wrap = 'pxg_geofesh_content_des')
    {
        $obj = new \stdClass();
        if (!empty($_data_geo)) {
            foreach ($_data_geo as $key => $_obj_data_geo) {
                if (isset($_obj_data_geo->code) && (strtolower($_obj_data_geo->code) == strtolower($_name))) {
                    $obj->name = $_obj_data_geo->code;
                    $obj->description = $_obj_data_geo->sid . '<div class="' . $_class_wrap . '">' . $_obj_data_geo->content . '</div>';
                    return $obj;
                }
            }
        }
        if (!empty($_name)) {
            $obj->name = $_name;
            $obj->description = '';
            return $obj;
        }
        return '';
    }
    /*GET Infor*/
    ////////////////////////////////////////// Extend //////////////////////////////////////////
    /*Extend optimization content*/
    public function pxg_geofeshx_date_tamnguyen($agr)
    {
        extract(shortcode_atts(
            array(
                'year' => '',
                'is_text' => ''
            ),
            $agr
        ));

        ob_start();
        if (empty($year) || !isset($year)) {
            $year = date('Y');
        }
    ?>
        <div class="pxg_geofeshx_date_tamnguyen">
            <div class="col-12 year" data-year="<?php echo $year ?>">
                <div class="text-center col-12" style="margin-bottom:8px"><input type="number" class="icheck_year_tamnguyen col-lg-6 col-sm-6 col-md-6 col-12" value="<?php echo $year ?>">
                    <div class="rest_year_tamnguyen"></div>
                </div>
                <div class="content"></div>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    var _leap_tam_nguyen_obj = $('.pxg_geofeshx_date_tamnguyen .year');
                    var _year = _leap_tam_nguyen_obj.data('year');
                    $('.rest_year_tamnguyen').html(pxg_geofesh_date_year_tamnguyen('.pxg_geofeshx_date_tamnguyen .year'));
                    $('.icheck_year_tamnguyen').on('keyup', function(e) {
                        if (e.keyCode == 13) {
                            $('.rest_year_tamnguyen').html(pxg_geofesh_date_year_tamnguyen(this));
                        }
                    });
                    $('.icheck_year_tamnguyen').on('input', function(e) {
                        $('.rest_year_tamnguyen').html(pxg_geofesh_date_year_tamnguyen(this));
                    });

                    function pxg_geofesh_date_year_tamnguyen(obj) {
                        var _yearx = $(obj).val();
                        if (!_yearx) {
                            _yearx = _year;
                        }
                        _yearx = parseInt(_yearx);
                        if (_yearx < 1864) {
                            _yearx = 1864;
                        }
                        var _khoiNguyen = 1864;
                        var X1 = _yearx - _khoiNguyen;
                        if (X1 >= 180) {
                            X1 = X1 % 180;
                        }
                        var _van = -1;
                        var _chuKy = '';
                        var _khoiSao = '';
                        var _xH = '';
                        if (X1 <= 60) {
                            if (X1 < 20) {
                                //vận 1;
                            } else if (X1 >= 20 && X1 < 40) {
                                //vận 2;//"Nhị Hắc"
                            } else {
                                //vận 3;//"Tam Bích"
                            }
                            _khoiSao = 'Nhất Bạch';
                            _chuKy = 'Thượng Nguyên';
                            _van = 1;
                        } else if (X1 > 60 && X1 <= 120) {
                            if (X1 < 80) {
                                //vận 4;//Vận chủ TRUNG NGUYÊN //"Tứ Lục"
                            } else if (X1 >= 80 && X1 < 100) {
                                //vận 5;//"Ngũ Hoàng Đại Sát"
                            } else {
                                //vận 6;//"Lục Bạch"
                            }
                            _khoiSao = 'Tứ Lục';
                            _chuKy = 'Trung Nguyên';
                            _van = 4;
                        } else {
                            if (X1 <= 140) {
                                //vận 7;//Vận chủ HẠ NGUYÊN //"Thất Xích"
                            } else if (X1 > 140 && X1 < 160) {
                                //vận 8;//"Bát Bạch"
                            } else {
                                //vận 9;//"Cửu Tử"
                            }
                            _khoiSao = 'Thất Xích';
                            _chuKy = 'Hạ Nguyên';
                            _van = 7;
                        }
                        var _chukyR = _khoiNguyen + 180;
                        while (_yearx > _chukyR) {
                            _chukyR += 180;
                        }
                        var _khoiKy = (_chukyR - 180);
                        _xH = 'Năm ' + _yearx + ' thuộc chu kỳ ' + _chuKy + ', vận chủ khởi sao ' + _khoiSao + ' (' + _van + ')';
                        _xH += '<div>Chu kỳ Tam Nguyên năm ' + _yearx + ' tồn tại</div>';
                        _xH += '<div class="pxg_geofeshx_date_tamnguyen_rest"><div class="head">Thượng Nguyên & chu kỳ Vận</div><div><span class="bb">● Vận 1</span>: từ năm ' + _khoiKy + ' - ' + (_khoiKy + 19) + '</div><div><span class="bb">● Vận 2</span>: từ năm ' + (_khoiKy + 20) + ' - ' + (_khoiKy + 39) + '</div><div><span class="bb">● Vận 3</span>: từ năm ' + (_khoiKy + 40) + ' - ' + (_khoiKy + 59) + '</div>';
                        _xH += '<div class="head">Trung Nguyên & chu kỳ Vận</div><div><span class="bb">● Vận 4</span>: từ năm ' + (_khoiKy + 60) + ' - ' + (_khoiKy + 79) + '</div><div><span class="bb">● Vận 5</span>: từ năm ' + (_khoiKy + 80) + ' - ' + (_khoiKy + 99) + '</div><div><span class="bb">● Vận 6</span>: từ năm ' + (_khoiKy + 100) + ' - ' + (_khoiKy + 119) + '</div>';
                        _xH += '<div class="head">Hạ Nguyên & chu kỳ Vận</div><div><span class="bb">● Vận 7</span>: từ năm ' + (_khoiKy + 120) + ' - ' + (_khoiKy + 139) + '</div><div><span class="bb">● Vận 8</span>: từ năm ' + (_khoiKy + 140) + ' - ' + (_khoiKy + 159) + '</div><div><span class="bb">● Vận 9</span>: từ năm ' + (_khoiKy + 160) + ' - ' + (_khoiKy + 179) + '</div></div>';
                        return _xH;
                    }
                });
            </script>
        </div>
    <?php
        return ob_get_clean();
    }
    public function pxg_geofeshx_date($agr)
    {
        extract(shortcode_atts(
            array(
                'leap' => '',
                'range' => '',
                'year' => '',
                'is_text' => ''
            ),
            $agr
        ));

        ob_start();
        $range = !empty($range) ? intval($range) : 10;
        if (empty($year) || !isset($year)) {
            $year = date('Y');
        }
        if ($range > 100) {
            $range = 100;
        }
    ?>
        <div class="pxg_geofeshx_date_leap">
            <div class="col-12 date" data-range="<?php echo $range ?>" data-leap="<?php echo $leap ?>" data-year="<?php echo $year ?>">
                <div class="text-center col-12" style="margin-bottom:8px"><input type="number" class="icheck_leap_year col-lg-6 col-sm-6 col-md-6 col-12" value="<?php echo $year ?>">
                    <div class="rest_check_leap_year"></div>
                </div>
                <div class="content"></div>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    var _leapobj = $('.pxg_geofeshx_date_leap .date');
                    var _leap = _leapobj.data('leap');
                    var _range = _leapobj.data('range');
                    var _year = _leapobj.data('year');
                    pxg_geofesh_datex_leap_single('.icheck_leap_year');
                    $('.pxg_geofeshx_date_leap .content').html(pxg_geofesh_datex_leap(_leap, _range, _year));
                    $('.icheck_leap_year').on('keyup', function(e) {
                        if (e.keyCode == 13) {
                            pxg_geofesh_datex_leap_single(this);
                        }
                    });
                    $('.icheck_leap_year').on('input', function(e) {
                        pxg_geofesh_datex_leap_single(this);
                    });

                    function pxg_geofesh_datex_leap_single(obj) {
                        var _yearIn = $(obj).val();
                        var _ileap_lunar = pxg_geofesh_datex_check_leap('lunar', _yearIn);
                        var _ileap_solar = pxg_geofesh_datex_check_leap('solar', _yearIn);
                        if (!_yearIn) {
                            _yearIn = _year;
                        }
                        _yearIn = parseInt(_yearIn);
                        if (_yearIn > 99999) {
                            _yearIn = 99999;
                        }
                        $('.rest_check_leap_year').html('Năm ' + _yearIn + ' (' + getYearCanChi(_yearIn) + ') - Dương Lịch: ' + ((_ileap_solar == true) ? 'nhuận' : ' không nhuận') + ' - Âm Lịch: ' + ((_ileap_lunar == true) ? 'nhuận' : ' không nhuận'));
                        $('.pxg_geofeshx_date_leap .content').html(pxg_geofesh_datex_leap(_leap, _range, _yearIn));
                    }

                    function pxg_geofesh_datex_check_leap(leap, year) {
                        if (leap == 'lunar') {
                            var x = year % 19;
                            return (x == 0 || x == 3 || x == 6 || x == 9 || x == 11 || x == 14 || x == 17) ? true : false;
                        }

                        return ((year % 100 == 0) ? (year % 400 == 0) : year % 4 == 0) ? true : false;
                    }

                    function pxg_geofesh_datex_leap(leap, range, year) {
                        var _yearx = parseInt(year);
                        if (leap == 'lunar') {
                            var _xH = '<table class="table table-striped"><tr class="bb"><td>Năm Âm Lịch</td><td>Nhuận hay không?</td></tr>';
                            for (var i = 0; i < range; i++) {
                                var x = _yearx % 19;
                                _xH += '<tr><td>' + _yearx + ' (' + getYearCanChi(_yearx) + ')</td><td>' + ((pxg_geofesh_datex_check_leap(leap, _yearx) == true) ? 'Nhuận' : '') + '</td></tr>';
                                _yearx++;
                            };
                        } else if (leap == 'solar') {
                            var _xH = '<table class="table table-striped"><tr class="bb"><td>Năm Dương Lịch</td><td>Nhuận hay không?</td></tr>';
                            for (var i = 0; i < range; i++) {
                                _xH += '<tr><td>' + _yearx + ' (' + getYearCanChi(_yearx) + ')</td><td>' + ((pxg_geofesh_datex_check_leap(leap, _yearx) == true) ? 'Nhuận' : '') + '</td></tr>';
                                _yearx++;
                            };
                        } else {
                            var _xH = '<table class="table table-striped"><tr class="bb"><td>Năm</td><td>Nhuận Dương Lịch hay không?</td><td>Nhuận Âm Lịch hay không?</td></tr>';
                            for (var i = 0; i < range; i++) {
                                var x = _yearx % 19;
                                _xH += '<tr><td>' + _yearx + ' (' + getYearCanChi(_yearx) + ')</td><td>' + ((pxg_geofesh_datex_check_leap('solar', _yearx) == true) ? 'Nhuận' : '') + '</td><td>' + ((pxg_geofesh_datex_check_leap('lunar', _yearx) == true) ? 'Nhuận' : '') + '</td></tr>';
                                _yearx++;
                            };
                        }
                        _xH += '</table>';
                        return _xH;
                    }
                });
            </script>
        </div>
    <?php
        return ob_get_clean();
    }
    public function pxg_geofesh_solar($agr)
    {
        //Tra cứu 24 tiết khí;
        extract(shortcode_atts(
            array(
                'range' => '',
                'year' => '',
                'is_text' => ''
            ),
            $agr
        ));

        ob_start();
        $range = !empty($range) ? intval($range) : 10;
        if (empty($year) || !isset($year)) {
            $year = date('Y');
        }
        if ($range > 10) {
            $range = 10;
        }
    ?>
        <div class="pxg_geofesh_solar">
            <div class="col-12 date" data-range="<?php echo $range ?>" data-year="<?php echo $year ?>">
                <div class="text-center col-12" style="margin-bottom:8px"><input type="number" class="pxg_geofesh_solar_in col-lg-6 col-sm-6 col-md-6 col-12" value="<?php echo $year ?>">
                    <div id="pxg_geofesh_solar_range">Hỗ trợ xem thời điểm giao tiết khí từ năm 0-6000</div>
                </div>
                <div class="content"></div>
            </div>
            <script>
                jQuery(document).ready(function($) {
                    var _tietKhis = <?php echo (json_encode(self::$TIETKHI)) ?>;
                    var _tietKhisDegree = <?php echo (json_encode(self::$TIET_KHI_DEGREE)) ?>;
                    var _leapobj = $('.pxg_geofesh_solar .date');
                    var _year = _leapobj.data('year');
                    var _range1 = 0;
                    var _range2 = 6000;
                    var _cur_year = new Date().getFullYear();
                    $('#pxg_geofesh_solar_range').html('Hỗ trợ xem thời điểm giao tiết khí từ năm ' + _range1 + '-' + _range2);
                    pxg_geofesh_solar_single('.pxg_geofesh_solar_in');
                    $('.pxg_geofesh_solar .content').html(pxg_geofesh_solar(_year));
                    $('.pxg_geofesh_solar_in').on('keyup', function(e) {
                        if (e.keyCode == 13) {
                            pxg_geofesh_solar_single(this);
                        }
                    });
                    $('.pxg_geofesh_solar_in').on('input', function(e) {
                        pxg_geofesh_solar_single(this);
                    });

                    function pxg_geofesh_solar_single(obj) {
                        var _yearIn = $(obj).val();
                        if (_yearIn < _range1 || _yearIn > _range2) {
                            _yearIn = _range2;
                        }
                        $('.pxg_geofesh_solar .content').html(pxg_geofesh_solar(_yearIn));
                    }

                    function pxg_geofesh_solar(year) {
                        var _yearx = parseInt(year);
                        var _xH = '<div class="pxg_geofesh_solar_info">Năm ' + _yearx + ' (' + getYearCanChi(_yearx) + ')</div>';
                        _xH += '<table class="table table-striped"><tr class="bb"><td>Kinh Độ Mặt Trời (KĐMT)</td><td>Tiết khí</td><td>Ngày Giờ</td></tr>';
                        for (var i = 0; i < _tietKhisDegree.length; i++) {
                            var _date_infor = pxg_geofesh_get_date_solar(_tietKhisDegree[i], _yearx);
                            if (_date_infor != undefined) {
                                _xH += '<tr><td>' + _tietKhisDegree[i] + '</td><td>' + _tietKhis[i] + '</td><td>' + _date_infor.date + '<div style="font-size:11px">KĐMT chính xác: ' + _date_infor.degree + '</div></td></tr>';
                            }
                        };
                        _xH += '</table>';
                        return _xH;
                    }
                });

                function pxg_geofesh_min(_arrs) {
                    if (_arrs.length == 0) return '';
                    var _min = _arrs[0];
                    _arrs.forEach(function(x) {
                        if (x.degree < _min.degree) {
                            _min = x;
                        }
                    });
                    return _min;
                }

                function pxg_geofesh_min_degree(_days, _cmonth, _cyear, _degree_ori) {
                    var _arrs = [];
                    for (var i = 0; i < _days.length; i++) {
                        for (var _h = 0; _h < 24; _h++) {
                            for (var _mi = 0; _mi < 60; _mi++) {
                                var _cur_degree = solarLongitude(jdAtVST(_days[i], _cmonth, _cyear, _h, _mi));
                                if (_cur_degree > _degree_ori && _cur_degree - _degree_ori < 1) {
                                    _arrs.push({
                                        degree: _cur_degree,
                                        date: (_days[i] + '/' + _cmonth + '/' + _cyear + " " + ((_h < 10) ? '0' + _h : _h) + ':' + ((_mi < 10) ? '0' + _mi : _mi))
                                    });
                                }
                            }
                        };
                    };
                    return pxg_geofesh_min(_arrs);
                }

                function pxg_geofesh_get_date_solar(_tietKhisDegree, _cyear) {
                    switch (_tietKhisDegree) {
                        case 0: {
                            /*Xuân Phân - Giữa xuân - 20,21,22/3*/
                            return pxg_geofesh_min_degree([16, 17, 18, 19, 20, 21, 22], 3, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 15: {
                            /*Thanh Minh - Trời trong sáng - 4,5,6/4*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6], 4, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 30: {
                            /*Cốc Vũ - Mưa rào - 20,21,22/4*/
                            return pxg_geofesh_min_degree([18, 19, 20, 21, 22], 4, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 45: {
                            /*Lập Hạ - Bắt đầu mùa hè - 5,6,7/5*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6, 7], 5, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 60: {
                            /*Tiểu Mãn - Lũ nhỏ, duối vàng - 21,22,23/5*/
                            return pxg_geofesh_min_degree([19, 20, 21, 22, 23], 5, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 75: {
                            /*Mang Chủng - Chòm sao Tua Rua mọc 5,6,7/6*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6, 7], 6, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 90: {
                            /*Hạ Chí - Giữa hè - 21,22,23/6*/
                            return pxg_geofesh_min_degree([17, 18, 19, 20, 21, 22, 23], 6, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 105: {
                            /*Tiểu Thử - Nóng nhẹ - 7,8,9/7*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6, 7, 8, 9], 7, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 120: {
                            /*Đại Thử - Nóng oi - 22,23,24/7*/
                            return pxg_geofesh_min_degree([18, 19, 20, 21, 22, 23, 24], 7, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 135: {
                            /*Lập Thu - Bắt đầu mua thu - 7,8,9/8*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6, 7, 8, 9], 8, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 150: {
                            /*Xử Thử - Mưa ngâu - 23,24,25/8*/
                            return pxg_geofesh_min_degree([18, 19, 20, 21, 22, 23, 24, 25], 8, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 165: {
                            /*Bạch Lộ - Nắng nhạt - 7,8,9/9*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6, 7, 8, 9], 9, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 180: {
                            /*Thu Phân - Giữa thu - 23,24,25/9*/
                            return pxg_geofesh_min_degree([18, 19, 21, 22, 23, 24, 25], 9, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 195: {
                            /*Hàn Lộ - Mát mẻ - 8,9,10/10*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6, 7, 8, 9, 10], 10, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 210: {
                            /*Sương Giáng - Sương mù xuất hiện - 23,24,25/10*/
                            return pxg_geofesh_min_degree([18, 19, 21, 22, 23, 24, 25], 10, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 225: {
                            /*Lập Đông - Bắt đầu mùa đông - 7,8,9/11*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6, 7, 8, 9], 11, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 240: {
                            /*Tiểu Tuyết - Tuyết xuất hiện - 22,23,24/11*/
                            return pxg_geofesh_min_degree([20, 21, 22, 23, 24], 11, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 255: {
                            /*Đại Tuyết - Tuyết dày - 7,8,9/12*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6, 7, 8, 9], 12, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 270: {
                            /*Đông Chí - Giữa đông - 21,22,23/12*/
                            return pxg_geofesh_min_degree([19, 20, 21, 22, 23], 12, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 285: {
                            /*Tiểu Hàn - Rét nhẹ - 5,6,7/1*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6, 7], 1, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 300: {
                            /*Đại Hàn - Rét đậm - 20,21,22/1*/
                            return pxg_geofesh_min_degree([18, 19, 20, 21, 22], 1, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 315: {
                            /*Lập Xuân - Bắt đầu mùa xuân - 4,5,6/2*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6], 2, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 330: {
                            /*Vũ Thủy - Mưa ẩm - 18,19,20/2*/
                            return pxg_geofesh_min_degree([16, 17, 18, 19, 20], 2, _cyear, _tietKhisDegree);
                            break;
                        }
                        case 345: {
                            /*Kinh Trập - Sâu nở - 5,6,7/3*/
                            return pxg_geofesh_min_degree([1, 2, 3, 4, 5, 6, 7], 3, _cyear, _tietKhisDegree);
                            break;
                        }
                    }
                }

                function INT(d) {
                    return Math.floor(d);
                }

                function jdn(dd, mm, yy) {
                    var a = INT((14 - mm) / 12);
                    var y = yy + 4800 - a;
                    var m = mm + 12 * a - 3;
                    var jd = dd + INT((153 * m + 2) / 5) + 365 * y + INT(y / 4) - INT(y / 100) + INT(y / 400) - 32045;
                    return jd;
                }

                function jdAtVST(dd, mm, yy, hour, minutes) {
                    var ret = jdn(dd, mm, yy);
                    return ret - 0.5 + (hour - 7) / 24.0 + minutes / 1440.0;
                }

                function solarLongitude(jd) {
                    var T, T2, dr, M, L0, C, lambda, theta, omega;
                    T = (jd - 2451545.0) / 36525;
                    T2 = T * T;
                    dr = Math.PI / 180;
                    M = 357.52910 + 35999.05030 * T - 0.0001559 * T2 - 0.00000048 * T * T2;
                    L0 = 280.46645 + 36000.76983 * T + 0.0003032 * T2;
                    C = (1.914600 - 0.004817 * T - 0.000014 * T2) * Math.sin(dr * M);
                    C = C + (0.019993 - 0.000101 * T) * Math.sin(dr * 2 * M) + 0.000290 * Math.sin(dr * 3 * M);
                    theta = L0 + C;
                    omega = 125.04 - 1934.136 * T;
                    lambda = theta - 0.00569 - 0.00478 * Math.sin(omega * dr);
                    lambda = lambda - 360 * (INT(lambda / 360));
                    return lambda;
                }
            </script>
        </div>
    <?php
        return ob_get_clean();
    }
    /*Extend optimization content*/
    ////////////////////////////////////////// Extend //////////////////////////////////////////

    public function pxg_geofesh_generate_popup($_id = '', $_zIndex = '')
    {
        $zI = (!empty($_zIndex)) ? 'z-index:' . $_zIndex . '!important' : '';
    ?>
        <!-- Modal -->
        <div class="modal fade" id="<?php echo $_id ?>" style="<?php echo $zI ?>" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title title"></h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body content"></div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">ĐÓNG</button>
                    </div>
                </div>
            </div>
        </div>
        <!-- //Modal -->
<?php
    }

    /*Get properties info*/
    function pxg_geofesh_date_info($agr)
    {
        extract(shortcode_atts(
            array(
                'name' => '',
                'type' => 'date',
                'is_text' => ''
            ),
            $agr
        ));

        ob_start();
        $_name = isset($agr['name']) ? $agr['name'] : '';
        $_date = isset($agr['type']) ? $agr['type'] : 'date';
        switch ($_date) {
            case 'date':
                $data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->dategoodbad, $_name, 'pxg_geofesh_date_content_des');
                break;
            case 'dongcong':
                $data_info = $this->pxg_geofesh_get_info_DATA_GEO(self::$pxg_geofesh_data->dongcong, $_name, 'pxg_geofesh_date_content_des');
                break;
        }
        if (!empty($data_info->description)) {
            echo $data_info->description;
        }
        return ob_get_clean();
    }
    /*Get properties info*/

    ////////////////////////////////////////// Functions DATA //////////////////////////////////////////
    public function pxg_geofesh_data_fnc($agr)
    {
        extract(shortcode_atts(
            array(
                'header' => '',
                'template' => '',
                'name' => '',
                'is_text' => ''
            ),
            $agr
        ));

        ob_start();

        switch ($name) {
            case "luc_nham": {
                    echo implode(', ', self::$NGAYLUCNHAMS);
                    break;
                }
            case "can": {
                    echo implode(', ', self::$CAN);
                    break;
                }
            case "chi": {
                    echo implode(', ', self::$CHI);
                    break;
                }
            case "tiet_khi": {
                    echo implode(', ', self::$TIETKHI);
                    break;
                }
            case "thap_than": {
                    echo implode(', ', self::$THAPTHAN);
                    break;
                }
            case "thap_nhi_truc": {
                    echo implode(', ', self::$THAPNHITRUC);
                    break;
                }
            case "kiet_hung_nhat": {
                    echo implode(', ', self::$KIETHUNGNHAT);
                    break;
                }
            case "kiet_hung_nhat_tot_xau": {
                    echo implode(', ', self::$KIETHUNGNHATTOTXAU);
                    break;
                }
            case "ngay_hoang_dao": {
                    echo implode(', ', self::$NGAYHOANGDAO);
                    break;
                }
            case "ngay_hac_dao": {
                    echo implode(', ', self::$NGAYHACDAO);
                    break;
                }
            case "cuu_cung_phi_tinh": {
                    echo implode(', ', self::$CUUCUNGPHITINH);
                    break;
                }
            case "luc_thap_hoa_giap": {
                    echo implode(', ', self::$DAYCANCHI_LUCTHAP_HOAGIAP);
                    break;
                }
            case "luc_thap_hoa_giap_khong_vong": {
                    echo "<table class='pxg_geofesh_info_table {$name}'>";
                    echo '<tr>';
                    foreach (self::$KHONGVONG as $header) {
                        $arr = explode(' ', $header);
                        echo '<th class="pxg_header">' . $this->pxg_implode_template(', ', $arr, 'pxg_create_slug', 'dia_chi') . '</th>';
                    }
                    echo '</tr>';
                    $rows = count(self::$KHONGVONG_LUCTHAP_HOAGIAP[0]);
                    for ($i = 0; $i < $rows; $i++) {
                        echo '<tr>';
                        foreach (self::$KHONGVONG_LUCTHAP_HOAGIAP as $columnData) {
                            echo "<td><span class='{$this->pxg_create_slug($columnData[$i])}'>{$columnData[$i]}</span></td>";
                        }
                        echo '</tr>';
                    }
                    echo '</table>';
                    break;
                }
            case "tuan_giap": {
                    echo implode(', ', self::$TUANGIAP_LUCTHAP_HOAGIAP);
                    break;
                }
            case "khong_vong": {
                    echo $this->pxg_implode_template(', ', self::$KHONGVONG, 'pxg_create_slug', 'dia_chi');
                    break;
                }
            case "tuan_giap_khong_vong": {
                    for ($i = 0; $i < count(self::$TUANGIAP_LUCTHAP_HOAGIAP); $i++) {
                        $tuan_giap = self::$TUANGIAP_LUCTHAP_HOAGIAP[$i];
                        $dia_chi_khong_vong = self::$KHONGVONG[$i];
                        $template_render = $template;
                        $template_render = str_replace('{tuan_giap}', $tuan_giap, $template_render);
                        $template_render = str_replace('{dia_chi}', $dia_chi_khong_vong, $template_render);
                        echo "<div>{$template_render}</div>";
                    }
                    break;
                }
            case "tieu_thanh": {
                    echo implode(', ', self::$TIEUTHANH);
                    break;
                }
            case "dai_thanh": {
                    echo implode(', ', self::$DAITHANH);
                    break;
                }
        }
        return ob_get_clean();
    }
    ////////////////////////////////////////// Functions DATA //////////////////////////////////////////

    ////////////////////////////////////////// Functions Helper //////////////////////////////////////////
    public function pxg_implode_template($separator, $array, $transformFunction, $text_prefix = '', $text_subfix = '')
    {
        $transformedArray = array_map(function ($item) use ($transformFunction) {
            $transformedItem = call_user_func([$this, $transformFunction], $item);
            return [$item, $transformedItem];
        }, $array);

        $result = implode($separator, array_map(function ($item) use ($text_prefix, $text_subfix) {
            $class = $this->pxg_concatenate_string($text_prefix, $text_subfix, $item[1]);
            return "<span class='{$class}'>{$item[0]}</span>";
        }, $transformedArray));

        return $result;
    }

    public function pxg_concatenate_string($text_prefix, $text_subfix, $item)
    {
        $class = '';
        if (!empty($text_prefix)) {
            $class .= $text_prefix . '_';
        }
        $class .= $item;
        if (!empty($text_subfix)) {
            $class .= '_' . $text_subfix;
        }
        return $class;
    }

    public function pxg_create_slug($input, $separator = '_')
    {
        $transliterationTable = [
            'à' => 'a', 'á' => 'a', 'ả' => 'a', 'ã' => 'a', 'ạ' => 'a',
            'ă' => 'a', 'ằ' => 'a', 'ắ' => 'a', 'ẳ' => 'a', 'ẵ' => 'a', 'ặ' => 'a',
            'â' => 'a', 'ầ' => 'a', 'ấ' => 'a', 'ẩ' => 'a', 'ẫ' => 'a', 'ậ' => 'a',
            'è' => 'e', 'é' => 'e', 'ẻ' => 'e', 'ẽ' => 'e', 'ẹ' => 'e',
            'ê' => 'e', 'ề' => 'e', 'ế' => 'e', 'ể' => 'e', 'ễ' => 'e', 'ệ' => 'e',
            'ì' => 'i', 'í' => 'i', 'ỉ' => 'i', 'ĩ' => 'i', 'ị' => 'i',
            'ò' => 'o', 'ó' => 'o', 'ỏ' => 'o', 'õ' => 'o', 'ọ' => 'o',
            'ô' => 'o', 'ồ' => 'o', 'ố' => 'o', 'ổ' => 'o', 'ỗ' => 'o', 'ộ' => 'o',
            'ơ' => 'o', 'ờ' => 'o', 'ớ' => 'o', 'ở' => 'o', 'ỡ' => 'o', 'ợ' => 'o',
            'ù' => 'u', 'ú' => 'u', 'ủ' => 'u', 'ũ' => 'u', 'ụ' => 'u',
            'ư' => 'u', 'ừ' => 'u', 'ứ' => 'u', 'ử' => 'u', 'ữ' => 'u', 'ự' => 'u',
            'ỳ' => 'y', 'ý' => 'y', 'ỷ' => 'y', 'ỹ' => 'y', 'ỵ' => 'y',
            'đ' => 'd',
            'À' => 'A', 'Á' => 'A', 'Ả' => 'A', 'Ã' => 'A', 'Ạ' => 'A',
            'Ă' => 'A', 'Ằ' => 'A', 'Ắ' => 'A', 'Ẳ' => 'A', 'Ẵ' => 'A', 'Ặ' => 'A',
            'Â' => 'A', 'Ầ' => 'A', 'Ấ' => 'A', 'Ẩ' => 'A', 'Ẫ' => 'A', 'Ậ' => 'A',
            'È' => 'E', 'É' => 'E', 'Ẻ' => 'E', 'Ẽ' => 'E', 'Ẹ' => 'E',
            'Ê' => 'E', 'Ề' => 'E', 'Ế' => 'E', 'Ể' => 'E', 'Ễ' => 'E', 'Ệ' => 'E',
            'Ì' => 'I', 'Í' => 'I', 'Ỉ' => 'I', 'Ĩ' => 'I', 'Ị' => 'I',
            'Ò' => 'O', 'Ó' => 'O', 'Ỏ' => 'O', 'Õ' => 'O', 'Ọ' => 'O',
            'Ô' => 'O', 'Ồ' => 'O', 'Ố' => 'O', 'Ổ' => 'O', 'Ỗ' => 'O', 'Ộ' => 'O',
            'Ơ' => 'O', 'Ờ' => 'O', 'Ớ' => 'O', 'Ở' => 'O', 'Ỡ' => 'O', 'Ợ' => 'O',
            'Ù' => 'U', 'Ú' => 'U', 'Ủ' => 'U', 'Ũ' => 'U', 'Ụ' => 'U',
            'Ư' => 'U', 'Ừ' => 'U', 'Ứ' => 'U', 'Ử' => 'U', 'Ữ' => 'U', 'Ự' => 'U',
            'Ỳ' => 'Y', 'Ý' => 'Y', 'Ỷ' => 'Y', 'Ỹ' => 'Y', 'Ỵ' => 'Y',
            'Đ' => 'D'
        ];

        $str = strtr($input, $transliterationTable);
        $str = preg_replace('/[^\w]+/', $separator, $str);
        return strtolower($str);
    }
    public function pxg_geofesh_get_day_name($_year, $_month, $_day_number, $lang = 'VI')
    {
        $date = $_day_number . '-' . $_month . '-' . $_year;
        $nameOfDay = date('l', strtotime($date));
        if ($lang === 'EN') {
            return $nameOfDay;
        }
        //VI
        $idx_day_in_week = array_search($nameOfDay, self::$DAYS_EN);
        return self::$DAYS_VI[$idx_day_in_week];
    }

    public function PXG_IS_SEO_YOAST_ACTIVE()
    {
        return function_exists('_wpseo_activate');
    }

    public function PXG_IS_SEO_RANK_MATH_ACTIVE()
    {
        return class_exists('RankMath');
    }

    ////////////////////////////////////////// Functions Helper //////////////////////////////////////////

    ////////////////////////////////////////// Functions DATE Helper //////////////////////////////////////////
    public function pxg_geofesh_date_convert()
    {
        \date_default_timezone_set('Asia/Ho_Chi_Minh');
        $timezone = 7.0;
        $_day = isset($_POST['day']) ? $_POST['day'] : (isset($args['day']) ? $args['day'] : date('d'));
        $_month = isset($_POST['month']) ? $_POST['month'] : (isset($args['month']) ? $args['month'] : date('m'));/*from 0-11*/
        $_year = isset($_POST['year']) ? $_POST['year'] : (isset($args['year']) ? $args['year'] : date('Y'));
        $_calendar = isset($_POST['calendar']) ? $_POST['calendar'] : (isset($args['calendar']) ? $args['calendar'] : 'lunar');

        $_day = (!empty($_day)) ? intval($_day) : $_day;
        $_month = (!empty($_month)) ? intval($_month) : $_month;
        $_year = (!empty($_year)) ? intval($_year) : $_year;

        $lunarLeapCurrentYear = $this->pxg_get_lunar_leap_from_solar(date('d'), date('m'), date('Y'), $timeZone);

        if ($_calendar === 'lunar') {
            $_dateSolar = $this->convertLunar2Solar($_day, $_month, $_year, $lunarLeapCurrentYear, $timezone);

            $_daySolar = $_dateSolar[0];
            $_monthSolar = $_dateSolar[1];
            $_yearSolar = $_dateSolar[2];

            $REST = (object)array(
                'solar' => (object)array('day' => $_daySolar, 'month' => $_monthSolar, 'year' => $_yearSolar),
                'lunar' => (object)array('day' => $_day, 'month' => $_month, 'year' => $_year)
            );

            echo json_encode($REST);
            if (isset($_POST['calendar'])) {
                exit();
            }
        }

        $_dateLunar = $this->convertSolar2Lunar($_day, $_month, $_year, $lunarLeapCurrentYear, $timezone);
        $_dayLunar = $_dateLunar[0];
        $_monthLunar = $_dateLunar[1];
        $_yearLunar = $_dateLunar[2];

        $REST = (object)array(
            'solar' => (object)array('day' => $_day, 'month' => $_month, 'year' => $_year),
            'lunar' => (object)array('day' => $_dayLunar, 'month' => $_monthLunar, 'year' => $_yearLunar)
        );

        echo json_encode($REST);
        if (isset($_POST['calendar'])) {
            exit();
        }
    }
    ////////////////////////////////////////// Functions DATE Helper //////////////////////////////////////////
    public function getAllSolarLunarRelated($solar_month, $solar_year, $lunar_days): array
    {
        $totalDaysInMonth = self::pxg_get_day_of_month($solar_month, $solar_year);

        $result = [];

        for ($day = 1; $day <= $totalDaysInMonth; $day++) {
            $lunarDate = $this->getLunarDate($day, $solar_month, $solar_year);
            $lunarDay = $lunarDate->day;
            $lunarMonth = $lunarDate->month;
            $lunarYear = $lunarDate->year;

            $dayCan = $this->getDayCan($lunarDate->jd);
            $dayChi = $this->getDayChi($lunarDate->jd);

            $monthCan = $this->getMonthCan($lunarMonth, $lunarYear);
            $monthChi = $this->getMonthChi($lunarMonth);

            $yearCan = $this->getYearCan($lunarYear);
            $yearChi = $this->getYearChi($lunarYear);

            if (in_array($lunarDay, $lunar_days)) {
                $result[] = [
                    'solar' => [
                        'day' => $day,
                        'month' => $solar_month,
                        'year' => $solar_year
                    ],
                    'lunar' => [
                        'day' => $lunarDay,
                        'month' => $lunarMonth,
                        'year' => $lunarYear,
                        'day_can' => $dayCan,
                        'month_can' => $monthCan,
                        'year_can' => $yearCan,
                        'day_chi' => $dayChi,
                        'month_chi' => $monthChi,
                        'year_chi' => $yearChi
                    ]
                ];
            }
        }
        return $result;
    }
    protected function pxg_geofesh_is_valid_license($userId = null): object
    {
        return (object) [
            'apps' => [
                (object) [
                    'is_active' => true,
                    'apps' => 'polyfengshui',
                ],
            ],
        ];
    }
}
