<?php


namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\Translation\TranslationPayloadBuilder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Translation API controller.
 *
 * WHY:
 * Provides runtime localization payloads
 * for SPA/frontend applications.
 */
class TranslationController extends Controller
{
    public function __construct(
        protected TranslationPayloadBuilder $payloadBuilder
    )
    {
    }

    /**
     * Return grouped translations.
     */
    public function index(
        Request $request
    ): JsonResponse
    {

        $locale = $request->string('locale')
            ->toString();

        $group = $request->string('group')
            ->toString();

        $locale = $locale ?: app()->getLocale();
        $group = $group ?: null;

        $frontendOnly = $request->boolean(
            'frontend'
        );

        $backendOnly = $request->boolean(
            'backend'
        );

        return response()->json(
            $this->payloadBuilder->build(
                locale: $locale,
                group: $group,
                frontendOnly: $frontendOnly,
                backendOnly: $backendOnly
            )
        );
    }
}
