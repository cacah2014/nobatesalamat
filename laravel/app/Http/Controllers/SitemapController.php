<?php

namespace App\Http\Controllers;

use App\Models\Doctor;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function robots(): Response
    {
        $content = "User-Agent: *\nAllow: /\nDisallow: /admin\nDisallow: /panel\n\nSitemap: " . route('sitemap') . "\n";

        return response($content, 200, ['Content-Type' => 'text/plain; charset=utf-8']);
    }

    public function sitemap(): Response
    {
        $urls = [
            ['loc' => url('/'), 'priority' => '1.0'],
            ['loc' => route('doctors.index'), 'priority' => '0.9'],
            ['loc' => route('doctors.index', ['visit' => 'online']), 'priority' => '0.8'],
            ['loc' => route('login'), 'priority' => '0.4'],
        ];

        foreach (Doctor::where('is_active', true)->get(['slug', 'updated_at']) as $doctor) {
            $urls[] = [
                'loc' => route('doctors.show', $doctor),
                'lastmod' => $doctor->updated_at?->toAtomString(),
                'priority' => '0.7',
            ];
        }

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n"
            . '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        foreach ($urls as $url) {
            $xml .= '<url><loc>' . e($url['loc']) . '</loc>'
                . (isset($url['lastmod']) ? '<lastmod>' . $url['lastmod'] . '</lastmod>' : '')
                . '<priority>' . $url['priority'] . '</priority></url>';
        }

        return response($xml . '</urlset>', 200, ['Content-Type' => 'application/xml; charset=utf-8']);
    }
}
