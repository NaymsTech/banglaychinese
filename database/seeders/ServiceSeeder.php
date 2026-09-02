<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Seed the Study in China service packages (lead-generation products).
     *
     * Pricing ladder (ascending by value):
     *   1. Guided Application         – 25,000 TK  (Get Eligibility Review)
     *   2. Full Application Service   – 60,000 TK  (Book Consultation)
     *   3. Elite Success Program      – 100,000 TK (Book Consultation)
     */
    public function run(): void
    {
        $services = [
            [
                'name'              => 'Guided Application',
                'slug'              => 'guided-application',
                'short_description' => 'For students who want expert direction but complete applications themselves.',
                'description'       => 'আপনি self-motivated এবং capable — নিজেই চীনে application করতে চান। কিন্তু একটা বড় সমস্যা: সঠিক তথ্য ও step-by-step গাইডেন্সের অভাব। এই Guided Application-এ পাবেন সম্পূর্ণ প্রক্রিয়ার detailed roadmap: document checklist, university shortlisting strategy, application timeline, এবং scholarship interview tips — সবকিছু এক জায়গায়।',
                'features'          => [
                    'Step-by-step Application Guide',
                    'Document Checklist & Templates',
                    'University Shortlisting Strategy',
                    'Application Timeline',
                    'Scholarship Interview Tips',
                ],
                'price'             => 25000,
                'cta_label'         => 'Get Eligibility Review',
                'duration'          => 'Self-paced',
                'status'            => true,
                'sort_order'        => 1,
            ],
            [
                'name'              => 'Full Application Service',
                'slug'              => 'full-application-service',
                'short_description' => 'Complete admission support from application to visa.',
                'description'       => 'Full Application Service: আপনার পুরো China Process আমাদের উপর ছেড়ে দিন। যারা চান কোনও ঝামেলা ছাড়া সম্পূর্ণ application process আমাদের expert টিম handle করুক, তাদের জন্য এই প্যাকেজ। আমরা করব: university shortlisting, document preparation ও verification, application submission, scholarship application, interview preparation, এবং visa guidance — সবকিছু step-by-step, personal mentorship-এর মাধ্যমে। আপনার শুধু focus করতে হবে পড়াশোনায়।',
                'features'          => [
                    'University Shortlisting',
                    'Document Preparation & Verification',
                    'Application Submission by Our Team',
                    'Scholarship Application',
                    'Interview Preparation Sessions',
                    'Visa Guidance Included',
                ],
                'price'             => 60000,
                'cta_label'         => 'Book Consultation',
                'duration'          => '~3 months',
                'status'            => true,
                'sort_order'        => 2,
            ],
            [
                'name'              => 'Elite Success Program',
                'slug'              => 'elite-success-program',
                'short_description' => 'Premium 12-month mentorship including language training and career guidance.',
                'description'       => 'Elite Success Program: Study থেকে Career পর্যন্ত ১-বছরের সম্পূর্ণ সাপোর্ট। Elite Success Program হলো আমাদের সবচেয়ে comprehensive package, যেখানে China admission, language, culture, student life — সবকিছু মিলিয়ে একটি ১-বছরের complete pathway। এই প্রোগ্রামে থাকছে: সম্পূর্ণ application support, ১ বছরের HSK language training, pre-departure cultural orientation, accommodation assistance, এবং China-তে পৌঁছানোর পর প্রথম মাসের settlement support। এটি শুধু admission নয় — এটি আপনার পুরো China journey-এর গ্যারান্টি।',
                'features'          => [
                    'Everything in Complete Support, plus:',
                    '1-Year HSK Language Training',
                    'Pre-Departure Cultural Orientation',
                    'Accommodation Assistance',
                    'First-Month Settlement Support in China',
                    'Complete China Journey Guarantee',
                ],
                'price'             => 100000,
                'cta_label'         => 'Book Consultation',
                'duration'          => '1 year',
                'status'            => true,
                'sort_order'        => 3,
            ],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['slug' => $service['slug']], $service);
        }
    }
}
