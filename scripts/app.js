

 document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');
  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'ru',
    initialView: 'dayGridMonth',
    editable: true,
    selectable: true,
    events: fcEventsFromPHP,

    select: function (info) {
      document.getElementById('eventTitle').value = '';
      document.getElementById('eventStart').value = info.startStr;
      document.getElementById('eventEnd').value = info.endStr || '';
      const modal = new bootstrap.Modal(document.getElementById('eventModal'));
      modal.show();
    },

    eventClick: function (info) {
      const title = prompt("Редактировать название события:", info.event.title);
      if (title !== null) {
        info.event.setProp('title', title);
        updateEvent(info.event);
      }
    },

    eventDrop: function (info) {
      updateEvent(info.event);
    },

    eventResize: function (info) {
      updateEvent(info.event);
    }
  });

  calendar.render();

  // Добавление
  document.getElementById('eventForm').addEventListener('submit', function (e) {
    e.preventDefault();
    const title = document.getElementById('eventTitle').value;
    const start = document.getElementById('eventStart').value;
    const end = document.getElementById('eventEnd').value;

    const newEvent = { title, start, end, allDay: false };

    fetch(`add_event.php?childID=${childID}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify(newEvent)
    })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          calendar.addEvent({
            id: data.id,
            title,
            start,
            end,
            allDay: false,
            color: '#1E90FF'
          });
        } else {
          alert('Ошибка при добавлении: ' + (data.error || 'неизвестно'));
        }
        bootstrap.Modal.getInstance(document.getElementById('eventModal')).hide();
      });
  });

  // Обновление
  function updateEvent(event) {
    fetch('update_event.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        id: event.id,
        title: event.title,
        start: event.start.toISOString(),
        end: event.end ? event.end.toISOString() : null,
        allDay: event.allDay
      })
    })
    .then(res => res.json())
    .then(data => {
      if (!data.success) {
        alert('Ошибка при обновлении: ' + (data.error || ''));
      }
    });
  }
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


