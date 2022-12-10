<?php

class DichVuCC_BaseAPI
{
    const BASE_URL = "https://dichvu.cc/api";
    private $api_key = null;
    public function __construct($api_key = null)
    {
        if (empty($api_key) || is_null($api_key))
            return die("Vui lòng nhập đầy đủ thông tin API Key của tài khoản.");
        $api_key = $api_key;
    }


    public function fetch($base, $payload = NULL, $headers = NULL, $method = "GET")
    {
        $options = [
            CURLOPT_URL => self::BASE_URL . "/" . $base,
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_CUSTOMREQUEST => $method
        ];
        $ch = curl_init();
        if (!is_null($payload)) {
            $options[CURLOPT_POST] = 1;
            $options[CURLOPT_POSTFIELDS] = $payload;
        }
        if (!is_null($headers) && is_array($headers)) $options[CURLOPT_HTTPHEADER] = $headers;
        curl_setopt_array($ch, $options);
        $response = curl_exec($ch);
        curl_close($ch);
        return $response;
    }

    /**
     * Lấy danh sách List ID Dịch Vụ
     * 
     * @method GET
     */
    public function services()
    {
        $response = json_decode($this->fetch("services"));
        if (isset($response->success) && $response->success == true) {
            // Dữ liệu được trả về
            // 
            $response_example = '{
                "success": true,
                "data": {
                    "tiktok": [
                        {
                            "id": 1,
                            "name": "Tiktok Follow",
                            "type": "follow",
                            "category": "Tiktok",
                            "rate": 150,
                            "min": 1000,
                            "max": 1000000
                        },
                        {
                            "id": 2,
                            "name": "Tiktok Seending",
                            "type": "comment",
                            "category": "Tiktok",
                            "rate": 100,
                            "min": 1000,
                            "max": 100000
                        }
                    ]
                }
            }';
        } else {
            // Tạo đơn hàng thất bại
            $response_errors_example = '{}';
        }
    }

    /**
     * Tạo đơn hàng tăng Follow Tiktok đi kèm với Views Video
     * 
     * @method POST
     * 
     * @param channel_name tên ghi nhớ
     * @param video_id đường dẫn link video của kênh cần tăng theo dõi
     * @param total số lượng theo dõi cần tăng cho tài khoản
     */
    public function store($channel_name = 'XXXX', $video_id = 'https://www.tiktok.com/@username/video/ID VIDEO', $total = 1000)
    {
        $params = array(
            'channel_name' => $channel_name,
            'video_id' => $video_id,
            'total' => 1000
        );
        $response = json_decode($this->fetch("services/1/tiktok?api_key={$this->api_key}", http_build_query($params), null, "POST"));
        if (isset($response->success) && $response->success == true) {
            // Tạo đơn hàng thành công
            // Dữ liệu được trả về
            // 
            $response_example = '{
                "success": true,
                "message": "Tạo đơn hàng hoàn tất.",
                "data": {
                    "id": 16, // ID Gói Khởi Tạo
                    "channel_name": "Test",
                    "channel_url": "Tên Username kênh tiktok",
                    "private_id": 0,
                    "user_id": 1, // ID Người Mua
                    "order_id": 17, // Đơn Hàng ID
                    "package_id": "FZEB2W", // Mã Định Danh Đơn Hàng
                    "services": 1, // ID Dịch Vụ 
                    "video_id": "ID video tiktok cần tăng",
                    "follow_default": 1000, // Số lượng Follow mua mặc định
                    "follow_percent": 100,
                    "follow_increase": 0, // Số lượng Follow đã tăng
                    "views_default": 1000,
                    "views_increase": 0, // Số lượng Views đã tăng cho Video
                    "views_min": 10,
                    "views_max": 15,
                    "likes_default": 0,
                    "likes_increase": 0,
                    "likes_percent": 0,
                    "seending_default": 0,
                    "seending_increase": 0,
                    "seending_percent": 0,
                    "share_default": 0,
                    "share_increase": 0,
                    "share_percent": 0,
                    "bookmark_default": 0,
                    "bookmark_increase": 0,
                    "bookmark_percent": 0,
                    "lock": "N", // Y = Đơn hàng đã bị khóa không thể tiếp tục tăng; N = Đơn hàng đang trong trạng thái mở có thể tăng 
                    "status": "CREATED", // CREATED = Tình trạng mới tạo; SUCCESS = ĐÃ HOÀN TẤT; RUNNING = ĐANG TRONG QUÁ TRÌNH CHẠY; LOCKED = DỊCH VỤ BỊ KHÓA; REFUND = HOÀN TIỀN DỊCH VỤ
                    "created_at": "2022-12-08T13:16:52.000000Z",
                    "updated_at": "2022-12-08T13:16:52.000000Z",
                    "finish_at": null, // Thời gian hoàn tất -> Nếu trả về trạng NULL đồng nghĩa là chưa hoàn thành nếu trả về 1 date có nghĩa là hoàn tất
                    "rate": 150, // Giá đồng cho mỗi 1 Follow
                    "price": 150000, // Số tiền phải bỏ ra khi mua
                    "balance_default": 7150000, // Số tiền mặc định ban đầu của tài khoản
                    "balance_spent": 7000000 // Số tiền tài khoản được thay đổi sau khi tạo đơn còn lại
                }
            }';
        } else {
            // Tạo đơn hàng thất bại
            $response_errors_example = '{
                "message": "The given data was invalid.",
                "errors": {
                    "video_id": [
                        "The video id field is required."
                    ]
                }
            }';
        }
    }


    /**
     * Lấy toàn bộ danh sách đơn hàng
     * 
     * @method GET
     * 
     * @param page trang bắt đầu từ 0
     * @param limit số lượng muốn hiền thị tối thiểu là 1 tối đa 1000 cho 1 page
     */
    public function get($page = 0, $limit = 50)
    {
        $response = json_decode($this->fetch("services/tiktok?api_key={$this->api_key}&limit=$limit&page=$page", null, null, "GET"));
        if (isset($response->success) && $response->success == true) {
            // Dữ liệu được trả về
            // 
            $response_example = '{
                "success": true,
                "data": [....],
                "summary": {
                    "totalRecord": 1,
                    "before": {
                        "nextPage": 1
                    }
                }
            }';
        } else {
            // Tạo đơn hàng thất bại
            $response_errors_example = '{
                "message": ".....",
                "errors": {
                    ....
                }
            }';
        }
    }
}


$api_key = "";

$call = new DichVuCC_BaseAPI($api_key);
