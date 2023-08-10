<template lang="html">
  <section :class="attr['section']">
    <div :class="attr['section__top']">
      <div :class="attr['section__top-title']">
        <svg
          enable-background="new 0 0 24 24"
          height="24px"
          viewBox="0 0 24 24"
          width="24px"
          fill="#000000"
        >
          <g>
            <rect
              fill="none"
              height="24"
              width="24"
            />
          </g>
          <g>
            <path
              d="M19,4h-1V2h-2v2H8V2H6v2H5C3.89,4,3.01,4.9,3.01,6L3,20c0,1.1,0.89,2,2,2h14c1.1,0,2-0.9,2-2V6C21,4.9,20.1,4,19,4z M19,20 H5V10h14V20z M19,8H5V6h14V8z M9,14H7v-2h2V14z M13,14h-2v-2h2V14z M17,14h-2v-2h2V14z M9,18H7v-2h2V18z M13,18h-2v-2h2V18z M17,18 h-2v-2h2V18z"
            />
          </g>
        </svg>
        <h2>{{ getMonth(active_date) }}</h2>
      </div>
      <div :class="attr['section__top-nav']">
        <svg
          enable-background="new 0 0 24 24"
          height="24px"
          viewBox="0 0 24 24"
          width="24px"
          fill="#000000"
          @click="prevMonth(active_date)"
        >
          <g>
            <rect
              fill="none"
              height="24"
              width="24"
            />
          </g>
          <g>
            <path
              d="M2,12c0,5.52,4.48,10,10,10c5.52,0,10-4.48,10-10S17.52,2,12,2C6.48,2,2,6.48,2,12z M20,12c0,4.42-3.58,8-8,8 c-4.42,0-8-3.58-8-8s3.58-8,8-8C16.42,4,20,7.58,20,12z M8,12l4-4l1.41,1.41L11.83,11H16v2h-4.17l1.59,1.59L12,16L8,12z"
            />
          </g>
        </svg>
        <svg
          enable-background="new 0 0 24 24"
          height="24px"
          viewBox="0 0 24 24"
          width="24px"
          fill="#000000"
          @click="nextMonth(active_date)"
        >
          <g>
            <rect
              fill="none"
              height="24"
              width="24"
            />
          </g>
          <g>
            <path
              d="M22,12c0-5.52-4.48-10-10-10C6.48,2,2,6.48,2,12s4.48,10,10,10C17.52,22,22,17.52,22,12z M4,12c0-4.42,3.58-8,8-8 c4.42,0,8,3.58,8,8s-3.58,8-8,8C7.58,20,4,16.42,4,12z M16,12l-4,4l-1.41-1.41L12.17,13H8v-2h4.17l-1.59-1.59L12,8L16,12z"
            />
          </g>
        </svg>
      </div>
    </div>
    <div :class="attr['section__calendar']">
      <!-- Day -->
      <div :class="attr['section__calendar-row']">
        <div
          :class="attr['section__calendar-row__cel']"
          v-for="(day, key) in days"
          :key="key"
        >
          {{ day }}
        </div>
      </div>
      <!-- dates -->
      <div
        :class="attr['section__calendar-row']"
        v-for="(date, key) in calendar"
        :key="key"
      >
        <div
          :class="[
            attr['section__calendar-row__cel'],
            attr['section__calendar-row__cel--date'],
            attr[`section__calendar-row__cel--${filteMonthDate(day)}`],
            select_day == $moment(day).format('YYYY-MM-DD') &&
              attr[`section__calendar-row__cel--selected`]
          ]"
          v-for="(day, key) in date.days"
          :key="key"
          @click="selectDate(day)"
        >
          {{ $moment(day).format('D') }}
          <p
            v-if="filteMonthDate(day) == 'active' && !getMobile"
            :class="attr['section__calendar-row__cel-note']"
          >
            Today
          </p>

          <!-- Icons -->
          <svg
            v-if="getEventCount(day) > 0 && !getMobile"
            height="24px"
            viewBox="0 0 24 24"
            width="24px"
            fill="#000000"
          >
            <path
              d="M0 0h24v24H0V0z"
              fill="none"
            />
            <path
              d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm0 16H5V9h14v10zM5 7V5h14v2H5zm5.56 10.46l5.93-5.93-1.06-1.06-4.87 4.87-2.11-2.11-1.06 1.06z"
            />
          </svg>
          <span v-if="getEventCount(day) > 0">{{ getEventCount(day) }}</span>
        </div>
      </div>
    </div>
  </section>
</template>

<script>
  import moment from 'moment'
  export default {
    inject: ['event_tasks'],
    data: () => ({
      calendar: [],
      active_date: '',
      select_day: '',
      days: ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']
    }),
    methods: {
      prevMonth(day) {
        let month_count = moment(day).month() + 1
        const startWeek = moment(day)
          .subtract(1, 'month')
          .startOf('month')
          .week()
        const endWeek =
          moment(day).subtract(1, 'month').endOf('month').week() + 1

        if (month_count == 1) {
          const date = moment(day).subtract(1, 'year')
          const start =
            moment(date).add(10, 'month').startOf('month').week() + 4
          const end = moment(date).add(10, 'month').endOf('month').week() + 5
          this.calendarGetter(start, end, date)
        } else {
          this.calendarGetter(startWeek, endWeek, day)
        }
      },
      nextMonth(day) {
        let month_count = moment(day).month() + 1
        const startDate = moment(day).add(1, 'month').startOf('month')
        const endDate = moment(day).add(1, 'month').endOf('month')
        const startWeek = startDate.isoWeek()
        const endWeek = endDate.isoWeek() + 1

        if (month_count === 12) {
          const nextYear = moment(day).add(1, 'year')
          const start = 1
          const end = 6
          this.calendarGetter(start, end, nextYear)
          return
        } else if (month_count === 11) {
          const startWeek = startDate.isoWeek()
          const endWeek = endDate.isoWeek()
        }
        this.calendarGetter(startWeek, endWeek, day)
      },
      calendarDateSetter() {
        const start_week = moment().startOf('month').week()
        const end_week = moment().endOf('month').week() + 1
        this.calendarGetter(start_week, end_week)
      },
      calendarGetter($start_week, $end_week, $date = moment(), $set) {
        let calendar = [],
          date = $date

        for (var week = $start_week; week < $end_week; week++) {
          calendar.push({
            week: week,
            days: Array(7)
              .fill(0)
              .map((n, i) =>
                moment($date)
                  .week(week)
                  .startOf('week')
                  .clone()
                  .add(n + i, 'day')
              )
          })
        }
        this.calendar = calendar
        if (this.calendar) {
          this.active_date = calendar[1].days[0]
        }
      },
      filteMonthDate(day) {
        let current_month = this.getMonth(this.active_date),
          month = this.getMonth(day),
          current_day = moment().format('DD-MM').toString(),
          date = moment(day).format('DD-MM').toString()
        if (current_month != month) {
          return 'other'
        }
        if (current_day == date) {
          return 'active'
        }
        return ''
      },
      getMonth(month) {
        let date =
          moment(month).format('MMMM').toString() + ' ' + moment(month).year()
        return date
      },
      getEventCount(date) {
        let count = ''
        this.event_tasks.forEach((event, key) => {
          if (this.$moment(date).format('YYYY-MM-DD') == event.date) {
            count++
          }
        })
        return count
      },
      selectDate(date) {
        let day = this.$moment(date).format('YYYY-MM-DD')
        if (day == this.select_day) {
          this.select_day = ''
          day = this.$moment().format('YYYY-MM-DD')
          this.$nuxt.$emit('select-calendar-day', day)
        } else {
          this.select_day = day
          this.$nuxt.$emit('select-calendar-day', day)
        }
      }
    },
    mounted() {
      setTimeout(() => {
        this.calendarDateSetter()
      }, 500)
    }
  }
</script>

<style lang="stylus" module="attr">
  .section
    background-color: var(--theme_white)
    &__top
      display: flex
      padding: 40px 20px 0
      justify-content: space-between
      &-title
        display: flex
        align-items: center
        h2
          font-size: 28px
          font-weight: var(--theme_bold)
        svg
          margin-right: 5px
      &-nav
        svg
          width: 34px
          height: 34px
          cursor: pointer
          transition: 0.4s ease-in-out
          &:hover
            transform: scale(1.2)
            fill: var(--theme_primary)
    &__calendar
      padding: 20px
      &-row
        display: flex
        flex-flow: row wrap
        &__cel
          position: relative
          flex: 0 0 calc(100% / 7)
          padding: 10px
          border: 1px solid var(--theme_primary)
          text-align: center
          background-color: var(--theme_primary)
          color: var(--theme_white)
          font-weight: var(--bold)
          cursor: pointer
          transition: 0.4s ease-in-out
          svg
            position: absolute
            top: 5px
            right: 25px
          span
            position: absolute
            top: 5px
            right: 2px
            font-size: 14px
            font-weight: var(--bold)
            padding: 2px 8px
            background-color: rgba(139, 247, 158, 0.8)
            border-radius: 60px
          &:hover
            transform: scale(1.05)
            z-index: 2
            opacity: 1
            border-radius: 3px
            box-shadow: 0px 2px 23px -3px rgba(45,196,65,0.75)
          &-note
            position: absolute
            left: calc(50% - 15px)
            font-size: 12px
          &--date
            padding: 20px 20px 30px
            font-size: 22px
            background-color: var(--theme_white)
            color: var(--theme_primary)
          &--other
            background-color: #a9a9a9
            color: var(--theme_white)
            pointer-event: none
            opacity: 0.5
          &--selected
            background-color: #ACFADF
            color: var(--theme_white)
            svg
              fill: #fff
          &--active
            background-color: var(--theme_primary)
            color: var(--theme_white)
            svg
              fill: #fff
            span
              background-color: rgba(255, 0, 0, 0.8)
  @media (max-width: 767px) and (min-width: 280px)
    .section
      &__calendar
        padding: 10px
        &-row
          &__cel
            position: relative
            flex: 0 0 calc(100% / 7)
            padding: 5px
            font-size: 16px
            span
              top: -3px
              right: -3px
              font-size: 10px
</style>
