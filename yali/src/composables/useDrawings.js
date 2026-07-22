import { ref, computed } from 'vue'

const API_BASE = '/api'

export function useDrawings() {
  const drawings = ref([])
  const currentPage = ref(1)
  const lastPage = ref(1)
  const total = ref(0)
  const loading = ref(false)
  const hasMore = computed(() => currentPage.value < lastPage.value)
  
  const loadDrawings = async (page = 1, append = false) => {
    if (loading.value) return
    
    loading.value = true
    try {
      const response = await fetch(
        `${API_BASE}/drawings?page=${page}&per_page=60`
      )
      const data = await response.json()
      
      if (append) {
        drawings.value.push(...data.data)
      } else {
        drawings.value = data.data
      }
      
      currentPage.value = data.current_page
      lastPage.value = data.last_page
      total.value = data.total
      
    } finally {
      loading.value = false
    }
  }
  
  const loadMore = () => loadDrawings(currentPage.value + 1, true)
  
  return {
    drawings,
    total,
    loading,
    hasMore,
    loadDrawings,
    loadMore
  }
}