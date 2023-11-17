function getRandomArrayValues(sourceArray, neededElements, counter) {
    var result = [];
    j = counter * neededElements;
    neededElements = j + neededElements;
    for (var i = j; i < neededElements; i++) {
        result.push(sourceArray[i]);
    }
    return result.join("");
}

function get_random_images(Y, img_collection, neededElements) {
    require(['jquery'], function() {
        var counters = 0;
        setInterval(function(){
            if ((counters * neededElements) >= img_collection.length) {
                counters = 0;
            }
            var getMeRandomElements = getRandomArrayValues(img_collection, neededElements, counters);
                document.getElementById('randomImageGallery').innerHTML = getMeRandomElements;
                counters++;
        }, 5000);
    });
}
