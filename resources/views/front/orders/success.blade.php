<x-front-layout title="Order Success">

    <div class="container text-center py-5">
        <h2>✅ تم تأكيد طلبك بنجاح</h2>

        <p>رقم الطلب: #{{ $order->id }}</p>

        <p>طريقة الدفع: الدفع عند الاستلام</p>

        <p>سيتم التواصل معك قريباً لتأكيد الطلب.</p>

        <a href="{{ route('home') }}" class="btn btn-primary mt-3">
            الرجوع للرئيسية
        </a>
    </div>

</x-front-layout>