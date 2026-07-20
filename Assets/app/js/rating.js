const allStarts = document.querySelectorAll('.start');
let current_rating = document.getElementById('rating');


allStarts.forEach((start, i) => {

    start.onclick = function() {

        let current_start_level = i + 1;
        current_rating.value = current_start_level;
        allStarts.forEach((start, j) => {
            if (current_start_level >= j + 1) {
                start.innerHTML = '<i class="fa-solid fa-star fa-fw fs-20"></i>';
            } else {
                start.innerHTML = '<i class="fa-light fa-star fa-fw fs-20"></i>';
            }
        })

    }

});

function loadRating(rating) {

    current_rating.value = rating;
    allStarts.forEach((start, j) => {
        if (rating >= j + 1) {
            start.innerHTML = '<i class="fa-solid fa-star fa-fw fs-20"></i>';
        } else {
            start.innerHTML = '<i class="fa-light fa-star fa-fw fs-20"></i>';
        }
    })

}



const allStartsRead = document.querySelectorAll('.start_read');

function loadRatingRead(rating) {

    allStartsRead.forEach((start_read, j) => {
        if (rating >= j + 1) {
            start_read.innerHTML = '<i class="fa-solid fa-star fa-fw fs-20"></i>';
        } else {
            start_read.innerHTML = '<i class="fa-light fa-star fa-fw fs-20"></i>';
        }
    })

}