function downloadFile(){
    var link = document.createElement('a');

    link.href = 'files/Resume - Renz John Magpantay.pdf';
    link.download = 'Resume-Renz-John-Magpantay.pdf';
    link.click();
}

function animateElementsInstantly() {
    var elements = document.getElementsByClassName("navigationAnimationDown");
    for (var i = 0; i < elements.length; i++) {
      var element = elements[i];
      element.classList.add("animate__animated", "animate__fadeInDown");
      element.style.animationDelay = i * 0.1 + "s";
    }
  

    var elements = document.getElementsByClassName("navigationAnimationRight");
    for (var i = 0; i < elements.length; i++) {
      var element = elements[i];
      element.classList.add("animate__animated", "animate__fadeInRight");
      element.style.animationDelay = i * 0.2 + "s";
    }

    var elements = document.getElementsByClassName("navigationAnimationLeft");
    for (var i = 0; i < elements.length; i++) {
      var element = elements[i];
      element.classList.add("animate__animated", "animate__fadeInLeft");
      element.style.animationDelay = i * 0.2 + "s";
    }
}
  // Call the function when the page loads
  window.addEventListener("DOMContentLoaded", animateElementsInstantly);
  
//new
var debounceTimeout;

function debounce(func, delay) {
  clearTimeout(debounceTimeout);
  debounceTimeout = setTimeout(func, delay);
}

function isElementInViewport(el) {
  var rect = el.getBoundingClientRect();
  return (
    rect.top >= 0 &&
    rect.left >= 0 &&
    rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
    rect.right <= (window.innerWidth || document.documentElement.clientWidth)
  );
}

function handleScroll() {
  var elementsUp = document.getElementsByClassName("scrollAnimationUp");
  var elementsLeft = document.getElementsByClassName("scrollAnimationLeft");
  var elementsRight = document.getElementsByClassName("scrollAnimationRight");

  for (var i = 0; i < elementsUp.length; i++) {
    var elementUp = elementsUp[i];
    if (isElementInViewport(elementUp) && !elementUp.classList.contains("animate__animated")) {
      elementUp.classList.add("animate__animated", "animate__fadeInUp");
      elementUp.style.animationDelay = i * 0.1 + "s";
      elementUp.style.animationDuration = "1s";
    }
  }

  for (var j = 0; j < elementsLeft.length; j++) {
    var elementLeft = elementsLeft[j];
    if (isElementInViewport(elementLeft) && !elementLeft.classList.contains("animate__animated")) {
      elementLeft.classList.add("animate__animated", "animate__fadeInLeft");
      elementLeft.style.animationDelay = j * 0.1 + "s";
      elementLeft.style.animationDuration = "1s";
    }
  }

  for (var r = 0; r < elementsRight.length; r++) {
    var elementRight = elementsRight[r];
    if (isElementInViewport(elementRight) && !elementRight.classList.contains("animate__animated")) {
      elementRight.classList.add("animate__animated", "animate__fadeInRight");
      elementRight.style.animationDelay = r * 0.1 + "s";
      elementRight.style.animationDuration = "1s";
    }
  }
}

function debouncedScrollHandler() {
  debounce(handleScroll, 100); // Adjust the delay (in milliseconds) as needed
}

function animateOnDOMContentLoaded() {
  handleScroll();
  window.addEventListener("scroll", debouncedScrollHandler);
}

document.addEventListener("DOMContentLoaded", animateOnDOMContentLoaded);

  