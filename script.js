const dropArea = document.getElementById('drop-area');
const fileInput = document.getElementById('images');
const fileList = document.getElementById('file-names');
const outputDiv = document.getElementById('output');
   
let secondsLeft = 10; // Definisikan variabel secondsLeft di luar event listener

document.addEventListener('click', (event) => {
    if (event.target && event.target.id === 'download-link') {
        // Reset timer ke 10 detik
        secondsLeft = 10;

        // Tampilkan timer di dalam div #timer
        const timerDiv = document.getElementById('timer');
        timerDiv.textContent = `Storage hosting menipis. Deleting files in ${secondsLeft} seconds...`;

        // Jalankan countdown timer
        const countdown = setInterval(() => {
            secondsLeft--;
            if (secondsLeft > 0) {
                timerDiv.textContent = `Storage hosting menipis. Deleting files in ${secondsLeft} seconds...`;
            } else {
                timerDiv.textContent = 'Deleting files now...';
                clearInterval(countdown);

                // Panggil fungsi rundelete untuk menghapus file
                rundelete();
            }
        }, 1000); // Interval 1 detik
    }
});


function rundelete() {
// Lakukan penghapusan file tanpa timer
fetch('delete_files.php?files=1&processed=1', {
    method: 'GET'
})
.then(response => {
    if (response.ok) {
        return response.text();
    } else {
        throw new Error('Failed to delete files.');
    }
})
.then(data => {
    console.log(data); // Debugging, bisa dihapus setelah selesai
    location.reload(); // Reload halaman setelah proses selesai
})
.catch(error => {
    console.error(error); // Debugging, bisa dihapus setelah selesai
});
}



// Menambahkan drag-and-drop functionality
dropArea.addEventListener('dragover', (event) => {
    event.preventDefault();
    dropArea.classList.add('dragover');
});

dropArea.addEventListener('dragleave', () => {
    dropArea.classList.remove('dragover');
});

dropArea.addEventListener('drop', (event) => {
    event.preventDefault();
    dropArea.classList.remove('dragover');
    const files = event.dataTransfer.files;
    updateFileList(files);
});

// Update file list dan input files
function updateFileList(files) {
    const dataTransfer = new DataTransfer();
    fileList.innerHTML = '';
    for (const file of files) {
        dataTransfer.items.add(file);
        const li = document.createElement('li');
        li.textContent = file.name;
        fileList.appendChild(li);
    }
    fileInput.files = dataTransfer.files;
}

// Handle manual file selection
fileInput.addEventListener('change', (event) => {
    updateFileList(fileInput.files);
});

// Fungsi untuk memproses gambar menggunakan AJAX
function processImages() {
    // Tampilkan pesan "Stamping your image...." di dalam div #output
    outputDiv.innerHTML = 'Process your image....';

    const formData = new FormData(document.getElementById('uploadForm'));

    // Menggunakan AJAX untuk mengirim data ke process.php
    const xhr = new XMLHttpRequest();
    xhr.open('POST', 'process.php', true);

    xhr.onload = function() {
        if (xhr.status === 200) {
            // Menampilkan output dari process.php di halaman
            outputDiv.innerHTML = xhr.responseText;
        } else {
            outputDiv.innerHTML = 'Error processing images!';
        }
    };

    xhr.send(formData);
}
