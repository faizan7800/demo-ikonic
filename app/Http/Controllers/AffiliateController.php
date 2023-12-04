
use App\Http\Controllers\Controller;
use App\Services\MerchantService;
use App\Models\Affiliate;

class AffiliateController extends Controller
{
    protected AffiliateService $affiliateService;

    public function __construct(AffiliateService $affiliateService)
    {
        $this->affiliateService = $affiliateService;
    }

    public function register(Merchant $merchant, string $email, string $name, float $commissionRate)
    {
        // Register a new affiliate for the merchant
        $affiliate = $this->affiliateService->register($merchant, $email, $name, $commissionRate);

        return response()->json(['message' => 'Affiliate registered successfully', 'affiliate' => $affiliate]);
    }
}
