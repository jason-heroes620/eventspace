<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Laravel\Facades\Image;
use Intervention\Image\Drivers\Gd\Driver;
use Throwable;

class CompressImage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:compress-image';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compressed Images';

    /**
     * Execute the console command.
     */

    public function handle()
    {
        $manager = new ImageManager(new Driver());
        $products = DB::table('products')->where('compressed_product_image', null)->where('product_image', '!=', '')->get();

        foreach ($products as $product) {
            $image = asset('storage') . '/img/' . $product->product_image;
            $image_name = explode('/', $image);
            $path = '/public/img/' . $image_name[sizeof($image_name) - 2] . '/compressed/';
            // dd($path);
            try {
                if (!Storage::exists($path)) {
                    Storage::makeDirectory($path);
                } else {
                    print_r('path exist');
                }
            } catch (Throwable $ex) {
                dd($ex);
            }

            $imageM = $manager->read(public_path() . '/storage/img/' . $image_name[sizeof($image_name) - 2] . '/' . $image_name[sizeof($image_name) - 1]);
            $new_path = $path . 'compressed_' . $image_name[sizeof($image_name) - 1];
            $imageM->resize(300, 200, function ($const) {
                $const->aspectRatio();
            })->save(public_path() . '/storage/img/' . $image_name[sizeof($image_name) - 2] . '/compressed/compressed_' . $image_name[sizeof($image_name) - 1]);

            DB::table('products')
                ->where('id', $product->id)
                ->update(['compressed_product_image' => $image_name[sizeof($image_name) - 2] . '/compressed/' . 'compressed_' . $image_name[sizeof($image_name) - 1]]);
        }
    }
}
