<?php

namespace App\Console\Commands;

use App\Services\FakeInstallService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\DomCrawler\Crawler;

class TestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test1';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    private FakeInstallService $fakeInstallService;
    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = 'https://www.etsy.com/listing/1063329364/reading-corner-decor-sign-posterboho?click_key=bd11fb0424cf60cc6661514800b6a146d7ca0eb4%3A1063329364&click_sum=543ea081&ref=shop_home_active_8&pro=1';
        $response = Http::get($url);
        $html = $response->body();

        $crawler = new Crawler($html);
        $images = $crawler->filter('.wt-list-unstyled.wt-display-flex-xs.wt-order-xs-1.wt-flex-direction-column-xs.wt-align-items-flex-end')->filter('li')->each(function (Crawler $node)
        {
            $img = $node->filter('img')->attr('data-src-delay');
            return [
                'link_img'=> $img
            ];
        });
        $data = [];
        foreach ($images as $image) {
            $image['link_img'] = str_replace('il_75x75', 'il_fullxfull', $image['link_img']);
            $data [] = $image;
        }
        dd($data);
        foreach ($data as $key => $da)
        {
            $client = new Client();
            $response = $client->get($da['link_img']);
            $content = $response->getBody()->getContents();
            Storage::put("public/images/img-{{$key}}", $content);

        }

    }


}
