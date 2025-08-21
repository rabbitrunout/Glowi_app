document.addEventListener('DOMContentLoaded', function () {
  const calendarEl = document.getElementById('calendar');

  const calendar = new FullCalendar.Calendar(calendarEl, {
    locale: 'ru',
    initialView: 'dayGridMonth',
    editable: true,
    selectable: true,

    // 🔥 Источники событий
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
      openModal('viewEventModal');
      document.getElementById('viewEventTitle').innerHTML = `<i data-lucide="calendar-days"></i> ${title}`;
      document.getElementById('viewEventDetails').innerText = desc;
      lucide.createIcons();
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

// --- Универсальное управление модалками ---
function openModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.style.display = "block";
  const overlay = document.getElementById('modalOverlay');
  if (overlay) overlay.style.display = "block";
}

function closeModal(id) {
  const modal = document.getElementById(id);
  if (modal) modal.style.display = "none";
  const overlay = document.getElementById('modalOverlay');
  if (overlay) overlay.style.display = "none";
}

// закрытие по клику вне окна
window.addEventListener("click", function(event) {
  if (event.target.classList.contains("modal")) {
    event.target.style.display = "none";
  }
  if (event.target.id === 'modalOverlay') {
    closeModal('viewEventModal');
  }
});

// --- Swiper ---
const swiper = new Swiper('.swiper', {
  loop: true,
  slidesPerView: 1,
  spaceBetween: 20,
  autoplay: { delay: 3000 },
  breakpoints: {
    768: { slidesPerView: 2 },
    1024: { slidesPerView: 3 },
  }
});

// --- Редактирование достижений ---
function editAchievement(ach) {
  document.getElementById("editID").value = ach.achievementID;
  document.getElementById("editTitle").value = ach.title;
  document.getElementById("editType").value = ach.type;
  document.getElementById("editDate").value = ach.dateAwarded;
  document.getElementById("editPlace").value = ach.place || "";
  document.getElementById("editMedal").value = ach.medal || "none";

  openModal();
}

function openModal() {
  document.getElementById("editModal").classList.add("active");
  document.getElementById("modalOverlay").classList.add("active");
}

function closeModal() {
  document.getElementById("editModal").classList.remove("active");
  document.getElementById("modalOverlay").classList.remove("active");
}

// Закрытие по клику на оверлей
document.addEventListener("click", function (e) {
  if (e.target.id === "modalOverlay") {
    closeModal();
  }
});


// --- инициализация иконок ---
document.addEventListener("DOMContentLoaded", () => {
  lucide.createIcons();
});
