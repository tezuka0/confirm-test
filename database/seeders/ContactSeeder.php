<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Contact;
use App\Models\Tag;
use Faker\Factory as FakerFactory;
use Illuminate\Database\Seeder;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = FakerFactory::create('ja_JP');

        $categoryIds = Category::pluck('id')->all();
        $tagIds = Tag::pluck('id')->all();

        for ($i = 0; $i < 20; $i++) {
            $digits = $faker->numberBetween(10, 11);

            $contact = Contact::create([
                'category_id' => $faker->randomElement($categoryIds),
                // 姓（first_name）と名（last_name）は、Fakerの lastName/firstName と対応させる
                'first_name' => $faker->lastName(),
                'last_name' => $faker->firstName(),
                'gender' => $faker->numberBetween(1, 3),
                'email' => $faker->unique()->safeEmail(),
                'tel' => '0'.$faker->numerify(str_repeat('#', $digits - 1)),
                'address' => $faker->address(),
                'building' => $faker->boolean(70) ? $faker->secondaryAddress() : null,
                'detail' => mb_substr($faker->realText(100), 0, 120),
            ]);

            $contact->tags()->attach(
                $faker->randomElements($tagIds, $faker->numberBetween(1, 3))
            );
        }
    }
}
