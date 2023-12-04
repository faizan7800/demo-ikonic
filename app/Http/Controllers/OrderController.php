use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    protected OrderService $orderService;

    public function __construct(OrderService $orderService)
    {
        $this->orderService = $orderService;
    }

    public function store(Request $request)
    {
        // Validate the request data as needed

        $orderData = [
            'merchant_id' => $request->input('merchant_id'),
            'subtotal' => $request->input('subtotal'),
            'customer_email' => $request->input('customer_email'),
            'default_commission_rate' => $request->input('default_commission_rate'),
        ];

        // Process the order using the OrderService
        $order = $this->orderService->processOrder($orderData);

        // Return a response as needed
        return response()->json(['message' => 'Order processed successfully', 'order' => $order]);
    }
}
