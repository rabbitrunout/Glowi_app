

document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

 const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'ru',
    initialView: 'dayGridMonth',
    editable: true,
    selectable: true,


    // 🔥 Вместо двух events — используем eventSources
    eventSources: [
      fcEventsFromPHP, // достижения, тренировки, соревнования
      {
        url: 'get_schedule.php?childID=' + childID,
        method: 'GET',
        failure: function() {
          alert('Ошибка загрузки расписания');
        }
      }
    ],

   eventDidMount: function(info) {
    if(info.event.extendedProps.eventType === 'schedule') {
      info.el.querySelector('.fc-event-title').innerHTML = '📅 ' + info.event.title;
    } else if(info.event.extendedProps.eventType === 'training') {
      info.el.querySelector('.fc-event-title').innerHTML = '🏋️ ' + info.event.title;
    } else if(info.event.extendedProps.eventType === 'competition') {
      info.el.querySelector('.fc-event-title').innerHTML = '🏆 ' + info.event.title;
    } else if(info.event.extendedProps.eventType === 'achievement') {
      info.el.querySelector('.fc-event-title').innerHTML = '🥇 ' + info.event.title;
    }
  },
    select: function(info) {
      const title = prompt("Название нового события:");
      if (!title) return;
      const type = prompt("Тип события: training/competition (оставьте пустым по умолчанию)");
      const newEvent = { title, start: info.startStr, end: info.endStr || '', allDay: false, eventType: type || 'training' };
      fetch(`add_event.php?childID=${childID}`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(newEvent)
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          let color = '#1E90FF'; // по умолчанию тренировка
          if(newEvent.eventType === 'competition') color = '#34a853';
          calendar.addEvent({
            id: data.id,
            title,
            start: info.startStr,
            end: info.endStr || '',
            allDay: false,
            color: color,
            extendedProps: { eventType: newEvent.eventType }
          });
        } else alert('Ошибка добавления: ' + (data.error||'неизвестно'));
      });
    },
    eventClick: function(info) {
      const title = info.event.title;
      const desc = info.event.extendedProps?.description || 'Без описания';
      openGlowiModal(title, desc);
    },
    eventDrop: updateEvent,
    eventResize: updateEvent
  });
  calendar.render();

  function updateEvent(info) {
    const event = info.event || info;
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
    .then(data => { if(!data.success) alert('Ошибка обновления: '+(data.error||'')) });
  }
});


function openGlowiModal(title, description) {
  document.getElementById('viewEventTitle').innerHTML = `<i data-lucide="calendar-days"></i> ${title}`;
  document.getElementById('viewEventDetails').innerText = description;
  document.getElementById('viewEventModal').style.display = 'block';
  document.getElementById('modalOverlay').style.display = 'block';
  lucide.createIcons();
}

function closeGlowiModal() {
  document.getElementById('viewEventModal').style.display = 'none';
  document.getElementById('modalOverlay').style.display = 'none';
}


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

function openModal() {
  document.getElementById("lessonModal").style.display = "block";
}
function closeModal() {
  document.getElementById("lessonModal").style.display = "none";
}
window.onclick = function(event) {
  let modal = document.getElementById("lessonModal");
  if (event.target == modal) {
    modal.style.display = "none";
  }
}

function editAchievement(data) {
  document.getElementById('overlay').style.display = 'block';
  document.getElementById('editModal').style.display = 'block';

  // заполняем форму данными
  document.getElementById('editID').value = data.achievementID;
  document.getElementById('editTitle').value = data.title;
  document.getElementById('editType').value = data.type;
  document.getElementById('editDate').value = data.dateAwarded;
  document.getElementById('editPlace').value = data.place || '';
  document.getElementById('editMedal').value = data.medal;
}

function closeModal() {
  document.getElementById('overlay').style.display = 'none';
  document.getElementById('editModal').style.display = 'none';
}

// закрытие по клику вне модалки
document.addEventListener('click', function(e) {
  if (e.target.id === 'overlay') {
    closeModal();
  }
});

// инициализация иконок
document.addEventListener("DOMContentLoaded", () => {
  lucide.createIcons();
});





