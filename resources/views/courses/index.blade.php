<x-app-layout
    :metaTitle="'কোর্সসমূহ | Banglay Chinese'"
    :metaDescription="'HSK ১–৪ প্রস্তুতি, চাইনিজ স্পিকিং মাস্টারি ও ক্যারিয়ার কোর্স — বাংলায় শিখুন চীনা ভাষা। Banglay Chinese-এ আজই ভর্তি হোন।'"
>
    {{-- Dark Green Hero Header --}}
    <div class="bg-[#0F5132] text-white py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <span class="text-emerald-300 text-sm font-semibold tracking-wide">কোর্সসমূহ</span>
            <h1 class="text-3xl md:text-4xl font-bold mt-2">বাংলায় শিখুন চীনা ভাষা</h1>
            <p class="mt-2 text-emerald-100 max-w-2xl">HSK প্রস্তুতি, স্পিকিং প্র্যাকটিস আর শিশুদের জন্য মজার ক্লাস — আপনার জন্য সঠিক কোর্সটি বেছে নিন।</p>
        </div>
    </div>

    {{-- Main Content Section --}}
    <div class="bg-gray-50/50 py-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            @if($courses->isEmpty())
                <p class="py-16 text-center text-slate-400">কোনো কোর্স পাওয়া যায়নি। শীঘ্রই নতুন কোর্স আসছে!</p>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    @foreach($courses as $course)
                        <x-course-card :course="$course" />
                    @endforeach
                </div>

                <div class="mt-12">
                    {{ $courses->links() }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
