<template lang="html">
  <section :class="attr['section']">
    <div :class="attr['section__top']">
      <h1 :class="attr['section__top-title']">{{ active_date }}</h1>
      <h2 :class="attr['section__top-sub']">
        {{ $moment(active_date).format('dddd') }}
      </h2>
    </div>
    <!-- List of Notes -->
    <div :class="attr['section__bottom']">
      <h2 :class="attr['section__bottom-title']">Events</h2>
      <div :class="attr['section__bottom-container']">
        <div
          v-if="records.length"
          v-for="(data, key) in records"
          :key="key"
          :class="attr['section__bottom-container__note']"
        >
          <p :class="attr['section__bottom-container__note-item']">
            <span>{{ data.start }} - {{ data.end }}</span>
            {{ data.event }}
          </p>
          <div :class="attr['section__bottom-container__note-action']">
            <svg
              xmlns="http://www.w3.org/2000/svg"
              enable-background="new 0 0 24 24"
              height="24px"
              viewBox="0 0 24 24"
              width="24px"
              fill="#000000"
              @click="updateEvent(data.id)"
            >
              <g>
                <path
                  d="M0,0h24v24H0V0z"
                  fill="none"
                />
              </g>
              <g>
                <g>
                  <path
                    d="M19,3h-4.18C14.4,1.84,13.3,1,12,1S9.6,1.84,9.18,3H5C3.9,3,3,3.9,3,5v14c0,1.1,0.9,2,2,2h14c1.1,0,2-0.9,2-2V5 C21,3.9,20.1,3,19,3z M12,2.75c0.41,0,0.75,0.34,0.75,0.75S12.41,4.25,12,4.25s-0.75-0.34-0.75-0.75S11.59,2.75,12,2.75z M19,19H5 V5h14V19z"
                  />
                  <polygon
                    points="15.08,11.03 12.96,8.91 7,14.86 7,17 9.1,17"
                  />
                  <path
                    d="M16.85,9.27c0.2-0.2,0.2-0.51,0-0.71l-1.41-1.41c-0.2-0.2-0.51-0.2-0.71,0l-1.06,1.06l2.12,2.12L16.85,9.27z"
                  />
                </g>
              </g>
            </svg>
            <svg
              xmlns="http://www.w3.org/2000/svg"
              height="24px"
              viewBox="0 0 24 24"
              width="24px"
              fill="#000000"
              @click="deleteEvent(data.id)"
            >
              <path
                d="M0 0h24v24H0V0z"
                fill="none"
              />
              <path
                d="M6 19c0 1.1.9 2 2 2h8c1.1 0 2-.9 2-2V7H6v12zM8 9h8v10H8V9zm7.5-5l-1-1h-5l-1 1H5v2h14V4h-3.5z"
              />
            </svg>
          </div>
        </div>
      </div>
      <div
        v-if="!open_form && !update_form"
        :class="attr['section__bottom-button']"
        @click="open_form = true"
      >
        Add Event
      </div>
    </div>
    <!-- Form Create -->
    <div
      v-if="open_form"
      :class="attr['section__bottom']"
    >
      <h2 :class="attr['section__bottom-title']">Add Event</h2>
      <validation-observer
        tag="form"
        ref="form"
        @submit.prevent="submit()"
      >
        <validation-provider
          :class="attr['section__form-group']"
          tag="div"
          name="company name"
          v-slot="{ errors }"
          :rules="{ required: true }"
        >
          <div
            :class="[
              attr['section__form-group__error'], //
              errors &&
                errors.length > 0 &&
                attr['section__form-group__error--active']
            ]"
          >
            <label
              for="first_name"
              :class="attr['section__form-group__label']"
            >
              Add event note*
            </label>
            <input
              type="text"
              :class="[
                attr['section__form-group__input'], //
                !errors.length &&
                  form_data.event &&
                  attr['section__form-group__input--active']
              ]"
              placeholder="e.g. Dinner"
              v-model="form_data.event"
            />
          </div>
        </validation-provider>
        <div :class="attr['section__form-inline']">
          <validation-provider
            :class="attr['section__form-group']"
            tag="div"
            name="type-of-inquiry"
            v-slot="{ errors }"
            :rules="{ required: true }"
          >
            <div
              :class="[
                attr['section__form-group__error'], //
                errors.length > 0 && attr['section__form-group__error--active']
              ]"
            >
              <label
                for="brand"
                :class="attr['section__form-group__label']"
              >
                Start *
              </label>
              <select
                :class="[
                  attr['section__form-group__input'],
                  attr['section__form-group__input--select'],
                  !errors.length &&
                    form_data.start &&
                    attr['section__form-group__input--active']
                ]"
                v-model="form_data.start"
              >
                <option
                  value=""
                  disabled
                  selected
                >
                  Select Hour
                </option>
                <option
                  v-for="(hrs, key) in hours"
                  :key="key"
                  :value="hrs"
                >
                  {{ hrs }}
                </option>
              </select>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                viewBox="0 0 36 36"
                xml:space="preserve"
                :class="attr['section__form-group__dropdown']"
              >
                <path
                  fill="#1A1818"
                  d="M6.26,11.01L18,22.72l11.74-11.7l3.6,3.6L18.01,29.94L2.66,14.61L6.26,11.01z"
                />
              </svg>
            </div>
          </validation-provider>
          <validation-provider
            :class="attr['section__form-group']"
            tag="div"
            name="type-of-inquiry"
            v-slot="{ errors }"
            :rules="{ required: true }"
          >
            <div
              :class="[
                attr['section__form-group__error'], //
                errors.length > 0 && attr['section__form-group__error--active']
              ]"
            >
              <label
                for="brand"
                :class="attr['section__form-group__label']"
              >
                End *
              </label>
              <select
                :class="[
                  attr['section__form-group__input'],
                  attr['section__form-group__input--select'],
                  !errors.length &&
                    form_data.end &&
                    attr['section__form-group__input--active']
                ]"
                v-model="form_data.end"
              >
                <option
                  value=""
                  disabled
                  selected
                >
                  Select Hour
                </option>
                <option
                  v-for="(hrs, key) in hours"
                  :key="key"
                  :value="hrs"
                >
                  {{ hrs }}
                </option>
              </select>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                viewBox="0 0 36 36"
                xml:space="preserve"
                :class="attr['section__form-group__dropdown']"
              >
                <path
                  fill="#1A1818"
                  d="M6.26,11.01L18,22.72l11.74-11.7l3.6,3.6L18.01,29.94L2.66,14.61L6.26,11.01z"
                />
              </svg>
            </div>
          </validation-provider>
          <button :class="attr['section__bottom-button']">Add Event</button>
        </div>
      </validation-observer>
      <div
        :class="[
          attr['section__bottom-button'],
          attr['section__bottom-button--cancel']
        ]"
        @click="open_form = false"
      >
        Cancel
      </div>
    </div>

    <!-- Update Form-->
    <div
      v-if="update_form"
      :class="attr['section__bottom']"
    >
      <h2 :class="attr['section__bottom-title']">Add Event</h2>
      <validation-observer
        tag="form"
        ref="form"
        @submit.prevent="submitUpdate()"
      >
        <validation-provider
          :class="attr['section__form-group']"
          tag="div"
          name="company name"
          v-slot="{ errors }"
          :rules="{ required: true }"
        >
          <div
            :class="[
              attr['section__form-group__error'], //
              errors &&
                errors.length > 0 &&
                attr['section__form-group__error--active']
            ]"
          >
            <label
              for="first_name"
              :class="attr['section__form-group__label']"
            >
              Add event note*
            </label>
            <input
              type="text"
              :class="[
                attr['section__form-group__input'], //
                !errors.length &&
                  form_data.event &&
                  attr['section__form-group__input--active']
              ]"
              placeholder="e.g. Dinner"
              v-model="form_data.event"
            />
          </div>
        </validation-provider>
        <div :class="attr['section__form-inline']">
          <validation-provider
            :class="attr['section__form-group']"
            tag="div"
            name="type-of-inquiry"
            v-slot="{ errors }"
            :rules="{ required: true }"
          >
            <div
              :class="[
                attr['section__form-group__error'], //
                errors.length > 0 && attr['section__form-group__error--active']
              ]"
            >
              <label
                for="brand"
                :class="attr['section__form-group__label']"
              >
                Start *
              </label>
              <select
                :class="[
                  attr['section__form-group__input'],
                  attr['section__form-group__input--select'],
                  !errors.length &&
                    form_data.start &&
                    attr['section__form-group__input--active']
                ]"
                v-model="form_data.start"
              >
                <option
                  value=""
                  disabled
                  selected
                >
                  Select Hour
                </option>
                <option
                  v-for="(hrs, key) in hours"
                  :key="key"
                  :value="hrs"
                >
                  {{ hrs }}
                </option>
              </select>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                viewBox="0 0 36 36"
                xml:space="preserve"
                :class="attr['section__form-group__dropdown']"
              >
                <path
                  fill="#1A1818"
                  d="M6.26,11.01L18,22.72l11.74-11.7l3.6,3.6L18.01,29.94L2.66,14.61L6.26,11.01z"
                />
              </svg>
            </div>
          </validation-provider>
          <validation-provider
            :class="attr['section__form-group']"
            tag="div"
            name="type-of-inquiry"
            v-slot="{ errors }"
            :rules="{ required: true }"
          >
            <div
              :class="[
                attr['section__form-group__error'], //
                errors.length > 0 && attr['section__form-group__error--active']
              ]"
            >
              <label
                for="brand"
                :class="attr['section__form-group__label']"
              >
                End *
              </label>
              <select
                :class="[
                  attr['section__form-group__input'],
                  attr['section__form-group__input--select'],
                  !errors.length &&
                    form_data.end &&
                    attr['section__form-group__input--active']
                ]"
                v-model="form_data.end"
              >
                <option
                  value=""
                  disabled
                  selected
                >
                  Select Hour
                </option>
                <option
                  v-for="(hrs, key) in hours"
                  :key="key"
                  :value="hrs"
                >
                  {{ hrs }}
                </option>
              </select>
              <svg
                xmlns="http://www.w3.org/2000/svg"
                xmlns:xlink="http://www.w3.org/1999/xlink"
                viewBox="0 0 36 36"
                xml:space="preserve"
                :class="attr['section__form-group__dropdown']"
              >
                <path
                  fill="#1A1818"
                  d="M6.26,11.01L18,22.72l11.74-11.7l3.6,3.6L18.01,29.94L2.66,14.61L6.26,11.01z"
                />
              </svg>
            </div>
          </validation-provider>
          <button :class="attr['section__bottom-button']">Update Event</button>
        </div>
      </validation-observer>
      <div
        :class="[
          attr['section__bottom-button'],
          attr['section__bottom-button--cancel']
        ]"
        @click="update_form = false"
      >
        Cancel
      </div>
    </div>
  </section>
</template>

<script>
  import moment from 'moment'
  export default {
    data: () => ({
      open_form: false,
      update_form: false,
      form_data: {
        event: '',
        start: '6:AM',
        end: '6:AM',
        date: ''
      },
      active_date: null,
      records: null
    }),
    inject: ['hours'],
    methods: {
      updateEvent(event_id) {
        this.$axios
          .$get(`web/calendar/${event_id}`)
          .then(({ record }) => {
            this.form_data = record
            setTimeout(() => {
              this.update_form = true
            }, 500)
          })
          .catch(() => {
            this.error({ statusCode: 500 })
          })
      },
      deleteEvent(event_id) {
        this.$axios
          .$delete(`web/calendar/${event_id}`)
          .then(({ records }) => {
            setTimeout(() => {
              this.fetchNote()
              this.$nuxt.$emit('update-calendar')
            }, 500)
          })
          .catch(() => {
            this.error({ statusCode: 500 })
          })
      },
      submit() {
        this.$refs.form.validate().then(success => {
          this.error_validation = false
          if (!success) {
            this.$scrollTo('.__v', {
              offset: -250
            })
            this.error_validation = true
            return
          } else {
            let form_data = {
              event: this.form_data.event,
              start: this.form_data.start,
              end: this.form_data.end,
              date: moment(this.active_date).format('YYYY-MM-DD')
            }

            this.$axios
              .$post('web/calendar', form_data)
              .then(() => {
                this.form_data = {
                  event: '',
                  start: '6:AM',
                  end: '6:AM'
                }
                this.$refs.form.reset()
                setTimeout(() => {
                  this.fetchNote()
                  this.$nuxt.$emit('update-calendar')
                  this.open_form = false
                }, 500)
              })
              .catch(() => {
                this.toggleTemplateModalStatus({
                  type: 'modal',
                  status: true,
                  item: {
                    title: 'Oops',
                    description:
                      'Sorry, Something went wrong. Please try again.'
                  }
                })
                setTimeout(() => {
                  this.toggleContentLoaderStatus({ status: false })
                }, 500)
              })
          }
        })
      },
      submitUpdate() {
        this.$refs.form.validate().then(success => {
          this.error_validation = false
          if (!success) {
            this.$scrollTo('.__v', {
              offset: -250
            })
            this.error_validation = true
            return
          } else {
            let form_data = {
              event: this.form_data.event,
              start: this.form_data.start,
              end: this.form_data.end,
              date: moment(this.active_date).format('YYYY-MM-DD'),
              _method: 'PATCH'
            }

            this.$axios
              .$post(`web/calendar/${this.form_data.id}`, form_data)
              .then(() => {
                this.form_data = {
                  event: '',
                  start: '6:AM',
                  end: '6:AM'
                }
                this.$refs.form.reset()
                setTimeout(() => {
                  this.fetchNote()
                  this.$nuxt.$emit('update-calendar')
                  this.update_form = false
                }, 500)
              })
              .catch(() => {
                this.toggleTemplateModalStatus({
                  type: 'modal',
                  status: true,
                  item: {
                    title: 'Oops',
                    description:
                      'Sorry, Something went wrong. Please try again.'
                  }
                })
                setTimeout(() => {
                  this.toggleContentLoaderStatus({ status: false })
                }, 500)
              })
          }
        })
      },
      fetchNote() {
        let date = moment(this.active_date).format('YYYY-MM-DD')
        this.$axios
          .$get(`web/calendar?date=${date}`)
          .then(({ records }) => {
            this.records = records
          })
          .catch(() => {
            this.error({ statusCode: 500 })
          })
      }
    },
    mounted() {
      setTimeout(() => {
        this.active_date = moment().format('MMM. DD, YYYY')
        this.fetchNote()
      }, 500)
    },
    created() {
      this.$nuxt.$on('select-calendar-day', payload => {
        this.active_date = moment(payload).format('MMM. DD, YYYY')
        this.fetchNote()
      })
    },
    beforeDestroy() {
      this.$nuxt.$off('select-calendar-day')
    },
    destroyed() {
      this.$nuxt.$off('select-calendar-day')
    }
  }
</script>

<style lang="stylus" module="attr">
  .section
    position: relative
    background-color: var(--theme_white)
    &__top
      padding: 40px 20px 20px
      background-color: #35155D
      &-title
        font-size: 28px
        font-weight: var(--bold)
        color: var(--theme_white)
      &-sub
        font-size: 54px
        font-weight: var(--semi)
        color: var(--theme_white)
    &__bottom
      padding: 20px
      &-title
        font-size: 18px
        font-weight: var(--bold)
      &-container
        padding: 20px 0
        &__note
          position: relative
          padding: 5px 0
          border-bottom: 1px solid #ACFADF
          &:before
            content: ''
            position: absolute
            top: calc(50% - 7px)
            left: 0
            width: 15px
            height: 15px
            border-radius: 20px
            background-color: #7C73C0
          &-item
            position: relative
            margin-top: 10px
            margin-left: 25px
            font-size: 16px
            font-weight: var(--bold)
            max-width: 75%
            span
              position: absolute
              top: -12px
              font-size: 12px
          &-action
            position: absolute
            top: calc(50% - 10px)
            right: 0
            svg
              cursor: pointer
              &:hover
                fill: var(--theme_primary)
      &-button
        margin-top: 5px
        padding: 10px 20px
        width: 100%
        border-radius: 5px
        background-color: #35155D
        font-size: 14px
        font-weight: var(--bold)
        color: var(--theme_white)
        text-align: center
        cursor: pointer
        &--cancel
          background-color: #D71313
        &:hover
          background-color: var(--theme_primary)
    &__form
      &-inline
        display: flex
        flex-flow: row wrap
        align-items: flex-start
        justify-content: space-between
        & ^[0..1]-group
            flex: 0 0 calc(50% - 10px)
      &-group
        position: relative
        &__error
          position: relative
          margin-bottom: 20px
          transition: .4s ease
        &__label
          display: block
          margin-bottom: 10px
          font-weight: var(--med)
          font-size: 18px
          color: var(--theme_black)
          transition: .4s ease
        &__input
          display: block
          width: 100%
          border: 1px solid #CCC
          padding: 12px 15px
          font-size: 16px
          letter-spacing: 0.24px
          color: var(--theme_black)
          resize: none
          transition: .4s ease
          border-radius: 10px
          border: 1px solid #35155D
          &::placeholder
            font-size: 16px
            letter-spacing: -0.4px
            color: rgba(0, 0, 0, 0.3)
          &--active
            border: 1px solid var(--theme_primary)
            color: var(--theme_primary)
          &:hover
            & + ^[-2]__dropdown
              transform: translateY(-50%) rotate(180deg)
        &__dropdown
          position: absolute
          top: 70%
          right: 15px
          width: 12px
          transform: translateY(-50%) rotate(0deg)
          transition: .4s ease
          &--small
            top: 50%
</style>
