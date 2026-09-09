@php
    use App\Services\SettingsService;
    $siteName = SettingsService::get('site_name', 'Banglay Chinese');
@endphp
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Update Payment Info | {{ $siteName }}</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" type="image/png" href="{{ asset('assets/logo.jpeg') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-emerald-50 via-white to-emerald-100 font-sans antialiased text-slate-800">
    <div class="flex min-h-screen items-center justify-center px-4 py-12">
        <div class="w-full max-w-lg">
            <div class="rounded-2xl bg-white p-8 shadow-xl sm:p-10">
                <h1 class="text-2xl font-bold text-gray-900">পেমেন্টের তথ্য আপডেট করুন</h1>
                <p class="mt-2 text-sm leading-relaxed text-slate-500">
                    অর্ডার <strong>#{{ $enrollment->id }}</strong> — {{ $enrollment->course?->title }}। আমাদের দল যে তথ্যটি সংশোধন করতে বলেছে তা আবার জমা দিন।
                </p>

                @if ($errors->any())
                    <div class="mt-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                        <ul class="list-inside list-disc space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('dashboard.payments.enrollment.resubmit', $enrollment) }}" class="mt-8 space-y-5">
                    @csrf
                    <div>
                        <label for="payment_method" class="mb-1.5 block text-sm font-semibold text-gray-700">পেমেন্ট পদ্ধতি</label>
                        <select id="payment_method" name="payment_method" required
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                            <option value="bkash" @selected(old('payment_method', $reviewMethod) === 'bkash')>bKash</option>
                            <option value="nagad" @selected(old('payment_method', $reviewMethod) === 'nagad')>Nagad</option>
                            <option value="bank" @selected(old('payment_method', $reviewMethod) === 'bank')>Bank Transfer</option>
                        </select>
                    </div>

                    <div>
                        <label for="transaction_id" class="mb-1.5 block text-sm font-semibold text-gray-700">ট্রানজেকশন আইডি</label>
                        <input id="transaction_id" type="text" name="transaction_id" value="{{ old('transaction_id', $reviewTransactionId) }}"
                               required placeholder="e.g. 9JQ2A3B4C5"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <div>
                        <label for="sender_number" class="mb-1.5 block text-sm font-semibold text-gray-700">প্রেরকের মোবাইল নম্বর</label>
                        <input id="sender_number" type="tel" name="sender_number" value="{{ old('sender_number', $reviewSenderNumber) }}"
                               required placeholder="01XXXXXXXXX"
                               class="w-full rounded-lg border border-gray-300 bg-white px-4 py-3 text-gray-900 placeholder-gray-400 transition-colors focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                    </div>

                    <button type="submit"
                            class="w-full rounded-lg bg-emerald-700 py-4 font-bold text-white shadow-lg transition-colors duration-300 hover:bg-emerald-800 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2">
                        তথ্য জমা দিন
                    </button>
                </form>

                <div class="mt-6 text-center">
                    <a href="{{ route('dashboard.index') }}" class="text-sm font-semibold text-emerald-700 hover:text-emerald-800">← ড্যাশবোর্ডে ফিরে যান</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
