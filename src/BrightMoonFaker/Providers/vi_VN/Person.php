<?php

namespace BrightMoonFaker\Providers\vi_VN;

use BrightMoonFaker\Providers\Person as BasePerson;

class Person extends BasePerson
{
    /**
     * Danh sách họ.
     *
     * @var string[]
     */
    protected $surnames = [
        'Bùi', 'Dương', 'Đào', 'Đặng', 'Đoàn', 'Đỗ', 'Hoàng', 'Hồ', 'Huỳnh', 'Lại', 'Lê', 'Lưu', 'Lý', 'Mạc', 'Mạch',
        'Mai', 'Ngô', 'Nguyễn', 'Phạm', 'Phan', 'Phùng', 'Tăng', 'Thái', 'Trần', 'Trịnh', 'Trương', 'Võ', 'Vũ', 'Vương',
    ];

    /**
     * Danh sách tên con trai.
     *
     * @var string[]
     */
    protected $nameMales = [
        'An', 'Anh', 'Ánh',
        'Bảo', 'Bách', 'Bắc', 'Bình', 'Bửu',
        'Công', 'Châu', 'Chương', 'Cường',
        'Dũng', 'Dương', 'Đại', 'Đạt', 'Đông', 'Đức',
        'Giang',
        'Hà', 'Hải', 'Hoàng', 'Hiếu', 'Huân', 'Hùng', 'Huy',
        'Khang', 'Khánh', 'Khiêm', 'Khoa', 'Khôi',
        'Lâm', 'Long', 'Lộc',
        'Minh',
        'Nam', 'Nghĩa', 'Ngọc',
        'Phát', 'Phong', 'Phú', 'Phước', 'Phương',
        'Quân',
        'Sang', 'Sinh', 'Sơn',
        'Tài', 'Tâm', 'Tân', 'Toàn', 'Trọng', 'Trung', 'Trường', 'Tuấn', 'Tùng',
        'Viễn', 'Vinh', 'Vũ', 'Vương',
    ];

    /**
     * Danh sách tên con gái.
     *
     * @var string[]
     */
    protected $nameFemales = [
        'An', 'Anh',
        'Châu', 'Cúc',
        'Dung', 'Duyên',
        'Đào',
        'Giang',
        'Hà', 'Hoa', 'Hồng', 'Huệ', 'Hương', 'Hường',
        'Khánh',
        'Lan', 'Liên', 'Linh', 'Loan',
        'Mai', 'My', 'Minh',
        'Nga', 'Ngân', 'Ngọc', 'Nguyệt', 'Nguyên', 'Nhi', 'Nhung', 'Như',
        'Oanh',
        'Phương', 'Phượng',
        'Quyên', 'Quỳnh',
        'Thanh', 'Thảo', 'Thuý', 'Thuỳ', 'Tiên', 'Trang',
        'Uyên',
        'Vân', 'Vy',
        'Xuyến',
        'Yến'
    ];

    /**
     * Danh sách tên đệm con trai.
     *
     * @var string[]
     */
    protected $middleNameMales = [
        'Anh', 'Minh', 'Phương', 'Văn', 'Xuân',
    ];

    /**
     * Danh sách tên đệm con gái.
     *
     * @var string[]
     */
    protected $middleNameFemales = [
        'Kim', 'Mai', 'Ngọc', 'Phương', 'Thị', 'Thanh', 'Xuân',
    ];
}
