<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <title>Search Keyword</title>
    @vite('resources/css/app.css')
    <!-- Include jQuery -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.16.9/xlsx.full.min.js"></script>
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body class="bg-gray-900 text-gray-100">
<div class="container mx-auto pt-[50px] pb-[100px]">
    <h1 class="text-primary-400 text-center text-3xl font-extrabold mb-8"><a href="/">Search Keyword</a></h1>

    <!-- Modify form to eliminate action and method attributes -->
    <form id="keyword-form" class="max-w-lg mx-auto p-8 bg-gray-800 shadow-lg rounded-lg" enctype="multipart/form-data">
        @csrf
        <div class="mb-6">
            <label for="file" class="block text-sm font-medium text-gray-300">Chọn file</label>
            <div class="relative mt-1">
                <input type="file" name="file" id="file" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer" required accept=".xlsx,.xls">
                <div class="flex items-center justify-center border border-gray-600 bg-gray-700 rounded-md py-2 px-3 text-gray-300 text-sm">
                    <span id="file-name">Chưa chọn file</span>
                </div>
            </div>
        </div>

        <div class="mb-6">
            <label for="api_key" class="block text-sm font-medium text-gray-300">API Key</label>
            <input type="text" name="api_key" id="api_key" placeholder="Nhập API Key" class="py-2 px-3 mt-1 block w-full border border-gray-600 rounded-md shadow-sm bg-gray-800 text-gray-100 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" required>
        </div>

        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
            Submit
        </button>
    </form>
    <div id="no-credit" class="w-full text-center hidden mt-3">
        <span class="text-red-500">API KEY đã hết Credit.</span>
    </div>
    <div id="loading" class="hidden">
        <div class="my-4 w-full flex justify-center">
            <div class="w-8 h-8 rounded-full animate-spin absolute
                            border-4 border-solid border-indigo-600 border-t-transparent"></div>
        </div>
    </div>
    <div id="error-message" class="w-full text-center hidden mt-3">
        <span class="text-red-500"></span>
    </div>
    <div id="results-container" class="mt-12 hidden">
        <div class="flex justify-between items-center">
            <h2 class="text-2xl font-semibold text-gray-100">Results</h2>
            <div class="mt-8 text-center">
                <a href="{{ route('export') }}" class="inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2 px-4 rounded-md shadow-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                    Download Excel
                </a>
            </div>
        </div>
        <div class="max-h-[600px] mt-8 relative" style="height: 600px; overflow-y: auto">
            <table id="results-table" class="min-w-full divide-y divide-gray-700 table-auto bg-gray-800 shadow-md rounded-lg">
                <thead class="bg-gray-700 sticky top-0">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                        Từ khóa
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                        Domain
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">
                        Thứ hạng
                    </th>
                </tr>
                </thead>
                <tbody id="results-body" class="bg-gray-800 divide-y divide-gray-700">
                <!-- Results will be dynamically inserted here -->
                </tbody>
            </table>
        </div>
    </div>
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        document.getElementById('file').addEventListener('change', function(event) {
            const file = event.target.files[0];
            const reader = new FileReader();
            const fileName = this.files.length > 0 ? this.files[0].name : 'Chưa chọn file';
            document.getElementById('file-name').textContent = fileName;
            reader.onload = function(e) {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, {type: 'array'});

                const worksheet = workbook.Sheets[workbook.SheetNames[0]];
                const json = XLSX.utils.sheet_to_json(worksheet);
                window.excelData = json;
            };

            reader.readAsArrayBuffer(file);
        });

        function arrayChunk(arr, chunkSize) {
            let chunks = [];
            for (let i = 0; i < arr.length; i += chunkSize) {
                chunks.push(arr.slice(i, i + chunkSize));
            }
            return chunks;
        }

        $('#keyword-form').on('submit', function(event) {
            event.preventDefault();
            $('#loading').removeClass('hidden');
            $('#no-credit').addClass('hidden');
            if (!window.excelData) {
                $('#error-message').removeClass('hidden').find('span').text('Please select an Excel file first.');
                return;
            } else {
                $('#error-message').addClass('hidden');
            }

            let keywords = arrayChunk(window.excelData, 100);
            let now = new Date();
            let currentTimeId = now.toISOString().replace(/[-:T]/g, '').split('.')[0];
            const apiKey = $('#api_key').val();
            let promises = keywords.map(chunk => {
                const formData = new FormData();
                formData.append('id', currentTimeId);
                formData.append('api_key', apiKey);
                formData.append('keywords', JSON.stringify(chunk));
                return $.ajax({
                    url: '{{ route('search-keyword') }}',
                    method: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false
                });
            });

            Promise.all(promises)
                .then(responses => {
                    let allResults = [];
                    responses.forEach(response => {
                        if(response.code) {
                            $('#no-credit').removeClass('hidden');
                        }
                        if (response && response.length > 0) {
                            allResults = allResults.concat(response);
                            $('#no-results').removeClass('hidden');
                        }
                        $('#loading').addClass('hidden');
                    });

                    $('#results-body').empty();
                    window.dataKeywords = allResults;
                    if (allResults.length > 0) {
                        allResults.forEach(result => {
                            console.log('result ', result);
                            $('#results-body').append(`
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-100">${result.q || 'not found'}</td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-primary-500 underline"><a href="${result.domain}">${result.domain || 'not found'}</a></td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-400">${result.position || 'not found'}</td>
                    </tr>
                `);
                        });
                        $('#results-container').removeClass('hidden');
                        $('#loading').addClass('hidden');
                    } else {
                        $('#no-results').removeClass('hidden');
                    }
                })
                .catch(error => {
                    $('#error-message').removeClass('hidden').find('span').text('An error occurred.');
                    console.error('AJAX Error: ', error);
                });
        });
    </script>
</div>
</body>
</html>
