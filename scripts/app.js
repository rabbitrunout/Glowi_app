  
  
  document.addEventListener('DOMContentLoaded', function() {
        const calendarEl = document.getElementById('calendar');

        const calendar = new FullCalendar.Calendar(calendarEl, {
          initialView: 'dayGridMonth',
          height: 'auto',
          aspectRatio: 1.2, 
          events: window.calendarEvents || [], // ← сюда можно передать события PHP → JS
          eventDisplay: 'block',
          eventColor: '#ff66ff',
          eventTextColor: '#1a1a2e',
          dayMaxEventRows: true,
          fixedWeekCount: false,
        });
        calendar.render();
        // console.log("🟢 FullCalendar script loaded");
      });


  const swiper = new Swiper('.swiper', {
  loop: true,
  slidesPerView: 1,
  spaceBetween: 20,
  autoplay: {
    delay: 3000,
  },
  breakpoints: {
    768: { slidesPerView: 2 },
    1024: { slidesPerView: 3 },
  }
});