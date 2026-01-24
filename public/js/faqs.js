document.addEventListener('DOMContentLoaded', function () {
    // Category chip selection
    document.querySelectorAll('.chip').forEach(chip => {
        chip.addEventListener('click', () => {
            document.querySelectorAll('.chip')
                .forEach(c => c.classList.remove('selected'));

            chip.classList.add('selected');

            document.getElementById('category_id').value = chip.dataset.category;
        });
    });

    // Image upload and preview
    const uploadArea = document.getElementById('uploadArea');
    const fileInput = document.getElementById('faqImage');
    const previewImg = document.getElementById('previewImg');
    const imagePreview = document.getElementById('imagePreview');
    const removeImageBtn = document.getElementById('removeImage');

    // Click 
    uploadArea.addEventListener('click', function () {
        fileInput.click();
    });

    // File selection
    fileInput.addEventListener('change', function (e) {
        const file = e.target.files[0];
        if (file) {
            handleFile(file);
        }
    });

    // Drag and drop    
    uploadArea.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.style.borderColor = '#00d4aa';
        this.style.backgroundColor = '#2a3242';
    });

    uploadArea.addEventListener('dragleave', function (e) {
        e.preventDefault();
        this.style.borderColor = '#3a4556';
        this.style.background = '#252d3d';
    });

    uploadArea.addEventListener('drop', function (e) {
        e.preventDefault();
        this.style.borderColor = '#3a4556';
        this.style.background = '#252d3d';

        const file = e.dataTransfer.files[0];
        if (file && file.type.startsWith('image/')) {
            fileInput.files = e.dataTransfer.files;
            handleFile(file);
        }
    });

    // Handle file and preview
    function handleFile(file) {
        if (file.size > 5 * 1024 * 1024) {
            alert('File size exceeds 5MB limit.');
            return;
        }

        if (file.type !== 'image/png' && file.type !== 'image/jpg' && file.type !== 'image/jpeg') {
            alert('Only PNG and JPG formats are allowed.');
            return;
        }

        const reader = new FileReader();
        reader.onload = function (e) {
            previewImg.src = e.target.result;
            uploadArea.style.display = 'none';
            imagePreview.style.display = 'block';
        }
        reader.readAsDataURL(file);
    }

    // Remove image
    removeImageBtn.addEventListener('click', function () {
        fileInput.value = '';
        previewImg.src = '';
        imagePreview.style.display = 'none';
        uploadArea.style.display = 'block';
        document.getElementById('remove_image').value = '1';
    })
});