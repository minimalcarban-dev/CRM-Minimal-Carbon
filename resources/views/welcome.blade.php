<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title> UNAUTHORIZED </title>
    @vite('resources/css/app.css')
</head>

<body class="bg-white font-serif min-h-screen items-center justify-center">

    <section class="min-h-screen flex items-center justify-center">
        <div class="container mx-auto">
            <div class="flex justify-center">
                <div class="w-full sm:w-10/12 md:w-8/12 text-center">
                    <div class="w-full h-[350px] sm:h-[400px] bg-center bg-no-repeat bg-contain bg-[length:70%]"
                        style="background-image: url('{{ asset('images/404.gif') }}');">
                        <h1 class="text-center text-black text-6xl sm:text-7xl md:text-8xl pt-6 sm:pt-8">
                            Wrong Way...
                        </h1>
                    </div>


                    <div class="mt-[-50px]">
                        <h3 class="text-2xl text-black sm:text-3xl font-bold mb-4">
                            Looks like you're lost
                        </h3>
                        <p class="mb-6 text-black sm:mb-5">
                            The page you are looking for does not exist.<br /> Click the button below to return to the
                            expected
                            page.
                        </p>

                        <a href="{{ url('/admin/login') }}"
                            class="inline-block bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg px-6 py-3 my-5">
                            Go to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

</body>

</html>