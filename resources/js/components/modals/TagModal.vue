<template>
  <Modal
    @close="$emit('close')"
    :headline="__('Edit tags')"
    :initial-focus="comboInput"
    :action-loading="form.processing"
    @action="save"
    :auto-close="false"
  >
    <pre>{{ selectedTag }}</pre>
    <Combobox as="div" v-slot="{ open }" class="relative" v-model="selectedTag">
      <ComboboxInput
        ref="comboInput"
        @change="query = $event.target.value"
        :class="input"
        :display-value="() => null"
        :placeholder="__('Search for a tag...')"
      />

      <DropIn>
        <ComboboxOptions class="absolute z-10 origin-top-left p-1 mt-2 w-full space-y-1 rounded-md shadow-lg bg-white dark:bg-gray-700 ring-1 ring-black ring-opacity-5 focus:outline-none">
          <ComboboxOption
            as="template"
            v-for="tag in filteredTags"
            :key="tag.id"
            :value="tag"
            v-slot="{ active, selected }"
          >
            <li
              :class="[
                active ? classes.active : classes.inactive,
                classes.always,
                'flex space-x-2',
              ]"
            >
              <span class="h-3 w-3 rounded-full" :class="colors[tag.color]" aria-hidden="true"></span>
              <span>{{ tag.name }}</span>
            </li>
          </ComboboxOption>
          <ComboboxOption
            v-if="filteredTags.length === 0 && query"
            :value="{}"
            v-slot="{ active, selected }"
          >
            <li
              :class="[
                active ? classes.active : classes.inactive,
                classes.always
              ]"
            >
              Create new tag "{{ query }}"...
            </li>
          </ComboboxOption>
        </ComboboxOptions>
      </DropIn>
    </Combobox>

    <Loader v-if="fetching" />
    <div v-else class="pt-4 flex space-x-1 items-start justify-center">
      <HelpText v-if="tags.length === 0" class="text-center">{{ __('No tags have been added yet.') }}</HelpText>
      <OutlineBadge
        v-for="(tag, index) in tags"
        :key="tag.id"
        show-dismiss
        edit-color
        @dismiss="tags.splice(index, 1)"
        v-model:color="tag.color"
      >
        {{ tag.name }}
      </OutlineBadge>
    </div>
  </Modal>
</template>

<script>
import { computed, defineComponent, inject, ref, watch } from 'vue'
import { Combobox, ComboboxInput, ComboboxOption, ComboboxOptions } from '@headlessui/vue'
import Modal from '@/components/Modal'
import Loader from '@/components/Loader'
import OutlineBadge from '@/components/OutlineBadge'
import HelpText from '@/components/HelpText'
import menuItemClasses from '@/composition/menuItemClasses'
import inputClasses from '@/composition/inputClasses'
import fetchesStudentTags from '@/composition/fetchesStudentTags'
import { nanoid } from 'nanoid'
import isEmpty from 'lodash/isEmpty'
import random from 'just-random'
import tagColorKey from '@/composition/tagColorKey'
import DropIn from '@/components/transitions/DropIn'
import { useForm } from '@inertiajs/inertia-vue3'

export default defineComponent({
  components: {
    DropIn,
    ComboboxOption,
    ComboboxOptions,
    ComboboxInput,
    HelpText,
    OutlineBadge,
    Loader,
    Modal,
    Combobox,
  },
  emits: ['close'],
  props: {
    searchUrl: {
      type: String,
      required: true
    },
    fetchUrl: {
      type: String,
      required: true
    },
    saveUrl: {
      type: String,
      required: true
    },
  },

  setup (props) {
    const { input } = inputClasses()
    const classes = menuItemClasses()
    const $http = inject('$http')
    const fetching = ref(false)
    const query = ref('')
    const tags = ref([])
    const selectedTag = ref()
    const { fetchAllTags, allTags } = fetchesStudentTags()
    const comboInput = ref()
    const colors = tagColorKey()
    const form = useForm({
      tags: [],
    })
    const fetchInitialTags = async () => {
      fetching.value = true
      const { data } = await $http.get(props.fetchUrl)
      tags.value = data

      fetching.value = false
    }
    const filteredTags = computed(() => {
      return allTags.value.filter(t => t.name.toLowerCase().includes(query.value.toLowerCase()))
    })
    const save = close => {
      form.tags = { ...tags.value }
      form.post(props.saveUrl, {
        preserveScroll: true,
        onSuccess () {
          close()
        }
      })
    }

    watch(selectedTag, (value) => {
      if (!value) {
        return
      }

      const newTag = isEmpty(value)
        ? {
          id: nanoid(),
          name: query.value,
          color: colors[random(Object.keys(colors))]
        }
        : value

      tags.value.push(newTag)
      query.value = ''
      selectedTag.value = null
    })
    fetchInitialTags()
    fetchAllTags()

    return {
      input,
      query,
      tags,
      fetching,
      filteredTags,
      classes,
      selectedTag,
      comboInput,
      save,
      form,
      colors,
    }
  }
})
</script>
