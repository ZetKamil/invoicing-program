<?php

namespace App\Services;

use DomainException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeminiService
{
    protected ?string $apiKey;
    protected string $model;
    protected string $baseUrl = 'https://generativelanguage.googleapis.com/v1beta/models';

    public function __construct()
    {
        $this->apiKey = config('services.gemini.api_key');
        $this->model  = config('services.gemini.model', 'gemini-1.5-flash');
    }

    /**
     * Generate structured blog post content using Gemini Structured Outputs.
     *
     * @param string $topic The topic or keyword for the article.
     * @param string $tone Tone of voice (e.g. 'professioneel', 'toegankelijk', 'expert').
     * @param string|null $category Optional category context.
     * @return array{title: string, slug: string, excerpt: string, sections: array<int, array{heading: string, level: string, content: string, image_caption?: string}>}
     *
     * @throws DomainException When API key is missing.
     * @throws RuntimeException When API call fails or response format is invalid.
     */
    public function generateStructuredArticle(string $topic, string $tone = 'professioneel', ?string $category = null): array
    {
        if (empty($this->apiKey)) {
            throw new DomainException('Google Gemini API-sleutel ontbreekt. Stel GEMINI_API_KEY in uw .env bestand in.');
        }

        $prompt = $this->buildPrompt($topic, $tone, $category);

        $payload = [
            'contents' => [
                [
                    'role'  => 'user',
                    'parts' => [
                        ['text' => $prompt],
                    ],
                ],
            ],
            'generationConfig' => [
                'responseMimeType' => 'application/json',
                'responseSchema'   => $this->getArticleJsonSchema(),
                'temperature'      => 0.7,
            ],
        ];

        /** @var Response $response */
        $response = Http::timeout(45)
            ->post("{$this->baseUrl}/{$this->model}:generateContent?key={$this->apiKey}", $payload);

        if (! $response->successful()) {
            $errorMessage = $response->json('error.message') ?? $response->body();
            throw new RuntimeException("Gemini API fout ({$response->status()}): {$errorMessage}");
        }

        $rawText = $response->json('candidates.0.content.parts.0.text');

        if (empty($rawText)) {
            throw new RuntimeException('Gemini API heeft een leeg antwoord geretourneerd.');
        }

        try {
            $parsed = json_decode($rawText, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException('Het antwoord van Gemini kon niet worden verwerkt als geldige JSON: ' . $e->getMessage());
        }

        return $parsed;
    }

    /**
     * Build the generative prompt for article drafting.
     */
    protected function buildPrompt(string $topic, string $tone, ?string $category): string
    {
        $context = $category ? " Binnen de categorie: '{$category}'." : '';

        return <<<PROMPT
Schrijf een hoogwaardig, diepgaand en professioneel blogartikel in het Nederlands over het onderwerp: "{$topic}".{$context}

Vereisten:
1. Taal: Strikte, vlekkeloze Nederlandse taal (Nederlands).
2. Toon van het artikel: {$tone}.
3. Structuur:
   - Een pakkende, SEO-geoptimaliseerde titel ('title').
   - Een schone URL-slug ('slug').
   - Een boeiende inleiding/samenvatting van 2-3 zinnen ('excerpt').
   - Tussen de 3 en 5 logisch opgebouwde secties ('sections').
4. Elke sectie moet bevatten:
   - 'heading': Heldere tussenkop.
   - 'level': Gebruik 'h2' voor hoofdsecties of 'h3' voor subsecties.
   - 'content': Rijke, informatieve inhoud in 1 tot 3 alinea's.
   - 'image_caption': Een concrete suggestie voor een bijpassende illustratie of foto.
PROMPT;
    }

    /**
     * Define the strict JSON schema for Gemini Structured Outputs.
     */
    protected function getArticleJsonSchema(): array
    {
        return [
            'type'       => 'OBJECT',
            'properties' => [
                'title'   => ['type' => 'STRING'],
                'slug'    => ['type' => 'STRING'],
                'excerpt' => ['type' => 'STRING'],
                'sections' => [
                    'type'  => 'ARRAY',
                    'items' => [
                        'type'       => 'OBJECT',
                        'properties' => [
                            'heading'       => ['type' => 'STRING'],
                            'level'         => [
                                'type' => 'STRING',
                                'enum' => ['h2', 'h3', 'h4'],
                            ],
                            'content'       => ['type' => 'STRING'],
                            'image_caption' => ['type' => 'STRING'],
                        ],
                        'required'   => ['heading', 'level', 'content'],
                    ],
                ],
            ],
            'required'   => ['title', 'slug', 'excerpt', 'sections'],
        ];
    }
}
